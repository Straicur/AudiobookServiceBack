<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Builder\NotificationBuilder;
use App\Entity\NotificationCheck;
use App\Enums\Cache\CacheValidTime;
use App\Enums\Cache\UserCacheKeys;
use App\Enums\Cache\UserStockCacheTags;
use App\Enums\UserRolesNames;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\Common\NewNotificationsSuccessModel;
use App\Model\Common\NotificationsSuccessModel;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Query\Common\SystemNotificationActivateQuery;
use App\Query\Common\SystemNotificationQuery;
use App\Repository\NotificationCheckRepository;
use App\Repository\NotificationRepository;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Service\TranslateServiceInterface;
use App\Tool\ResponseTool;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

use function count;

#[OA\Response(
    response   : 400,
    description: 'JSON Data Invalid',
    content    : new Model(type: JsonDataInvalidModel::class)
)]
#[OA\Response(
    response   : 401,
    description: 'User not authorized',
    content    : new Model(type: NotAuthorizeModel::class)
)]
#[OA\Response(
    response   : 403,
    description: 'User have no permission',
    content    : new Model(type: PermissionNotGrantedModel::class)
)]
#[OA\Response(
    response   : 404,
    description: 'Data not found',
    content    : new Model(type: DataNotFoundModel::class)
)]
#[OA\Tag(name: 'Notification')]
class NotificationController extends AbstractController
{
    public function __construct(private readonly AuthorizedUserServiceInterface $authorizedUserService, private readonly RequestServiceInterface $requestServiceInterface, private readonly NotificationRepository $notificationRepository, private readonly LoggerInterface $endpointLogger, private readonly TranslateServiceInterface $translateService, private readonly NotificationCheckRepository $checkRepository, private readonly TagAwareCacheInterface $stockCache) {}

    #[Route('/api/notifications', name: 'notifications', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::USER, UserRolesNames::RECRUITER])]
    #[OA\Post(
        description: 'Method get all notifications from the system for logged user',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: SystemNotificationQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: NotificationsSuccessModel::class),
            ),
        ]
    )]
    public function notifications(
        Request $request,
    ): Response {
        $systemNotificationQuery = $this->requestServiceInterface->getRequestBodyContent($request, SystemNotificationQuery::class);

        if ($systemNotificationQuery instanceof SystemNotificationQuery) {
            $user = $this->authorizedUserService::getAuthorizedUser();

            $systemNotificationSuccessModel = $this->stockCache->get(
                UserCacheKeys::USER_NOTIFICATIONS->value . $user->getId() . '_' . $systemNotificationQuery->getPage() . $systemNotificationQuery->getLimit(),
                function (ItemInterface $item) use ($systemNotificationQuery, $user): NotificationsSuccessModel {
                    $item->expiresAfter(CacheValidTime::FIVE_MINUTES->value);
                    $item->tag(UserStockCacheTags::USER_NOTIFICATIONS->value);

                    $userSystemNotifications = $this->notificationRepository->getUserNotifications($user);

                    $systemNotifications = [];

                    $minResult = $systemNotificationQuery->getPage() * $systemNotificationQuery->getLimit();
                    $maxResult = $systemNotificationQuery->getLimit() + $minResult;

                    foreach ($userSystemNotifications as $index => $notification) {
                        if ($index < $minResult) {
                            continue;
                        }

                        if ($index < $maxResult) {
                            $notificationCheck = $this->checkRepository->findOneBy([
                                'user'         => $user->getId(),
                                'notification' => $notification->getId(),
                            ]);

                            $systemNotifications[] = NotificationBuilder::read($notification, $notificationCheck);
                        } else {
                            break;
                        }
                    }

                    return new NotificationsSuccessModel(
                        $systemNotifications,
                        $systemNotificationQuery->getPage(),
                        $systemNotificationQuery->getLimit(),
                        (int) ceil(count($userSystemNotifications) / $systemNotificationQuery->getLimit()),
                    );
                }
            );

            return ResponseTool::getResponse($systemNotificationSuccessModel);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/notification/activate', name: 'notificationActivate', methods: ['PUT'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::USER, UserRolesNames::RECRUITER])]
    #[OA\Put(
        description: 'Method get is activating given notification so user can see if he read this notification',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: SystemNotificationActivateQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
            ),
        ]
    )]
    public function notificationActivate(
        Request $request,
    ): Response {
        $systemNotificationActivateQuery = $this->requestServiceInterface->getRequestBodyContent($request, SystemNotificationActivateQuery::class);

        if ($systemNotificationActivateQuery instanceof SystemNotificationActivateQuery) {
            $notification = $this->notificationRepository->find($systemNotificationActivateQuery->getNotificationId());

            if (null === $notification) {
                throw new DataNotFoundException([$this->translateService->getTranslation('NotificationDontExists')]);
            }

            $user = $this->authorizedUserService::getAuthorizedUser();

            $notificationCheck = $this->checkRepository->findOneBy([
                'user'         => $user->getId(),
                'notification' => $notification->getId(),
            ]);

            if (!$notificationCheck) {
                $notificationCheck = new NotificationCheck($user, $notification);
                $this->checkRepository->add($notificationCheck);
            }

            return ResponseTool::getResponse(httpCode: Response::HTTP_CREATED);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/new/notifications', name: 'newNotifications', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::USER, UserRolesNames::RECRUITER])]
    #[OA\Post(
        description: 'Method get amount of new notifications for logged user',
        requestBody: new OA\RequestBody(),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: NewNotificationsSuccessModel::class),
            ),
        ]
    )]
    public function newNotifications(): Response
    {
        $user = $this->authorizedUserService::getAuthorizedUser();
        $systemNotificationSuccessModel = new NewNotificationsSuccessModel(
            $this->notificationRepository->getUserActiveNotifications($user),
        );

        return ResponseTool::getResponse($systemNotificationSuccessModel);
    }
}
