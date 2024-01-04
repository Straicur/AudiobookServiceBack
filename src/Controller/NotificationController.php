<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Builder\NotificationBuilder;
use App\Entity\NotificationCheck;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
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
use App\Service\TranslateService;
use App\Tool\ResponseTool;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Response(
    response: 400,
    description: "JSON Data Invalid",
    content: new Model(type: JsonDataInvalidModel::class)
)]
#[OA\Response(
    response: 401,
    description: "User not authorized",
    content: new Model(type: NotAuthorizeModel::class)
)]
#[OA\Response(
    response: 403,
    description: "User have no permission",
    content: new Model(type: PermissionNotGrantedModel::class)
)]
#[OA\Response(
    response: 404,
    description: "Data not found",
    content: new Model(type: DataNotFoundModel::class)
)]
#[OA\Tag(name: "Notification")]
class NotificationController extends AbstractController
{
    /**
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param Request $request
     * @param RequestServiceInterface $requestServiceInterface
     * @param NotificationRepository $notificationRepository
     * @param LoggerInterface $endpointLogger
     * @param TranslateService $translateService
     * @param NotificationCheckRepository $checkRepository
     * @return Response
     * @throws InvalidJsonDataException
     */
    #[Route("/api/notifications", name: "notifications", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator", "User"])]
    #[OA\Post(
        description: "Method get all notifications from the system for logged user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: SystemNotificationQuery::class),
                type: "object"
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: NotificationsSuccessModel::class)
            )
        ]
    )]
    public function notifications(
        AuthorizedUserServiceInterface $authorizedUserService,
        Request                        $request,
        RequestServiceInterface        $requestServiceInterface,
        NotificationRepository         $notificationRepository,
        LoggerInterface                $endpointLogger,
        TranslateService               $translateService,
        NotificationCheckRepository    $checkRepository
    ): Response
    {
        $systemNotificationQuery = $requestServiceInterface->getRequestBodyContent($request, SystemNotificationQuery::class);

        if ($systemNotificationQuery instanceof SystemNotificationQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $userSystemNotifications = $notificationRepository->getUserNotifications($user);

            $systemNotifications = [];

            $minResult = $systemNotificationQuery->getPage() * $systemNotificationQuery->getLimit();
            $maxResult = $systemNotificationQuery->getLimit() + $minResult;

            foreach ($userSystemNotifications as $index => $notification) {
                if ($index < $minResult) {
                    continue;
                }

                if ($index < $maxResult) {

                    $notificationCheck = $checkRepository->findOneBy([
                        "user" => $user->getId(),
                        "notification" => $notification->getId()
                    ]);

                    $systemNotifications[] = NotificationBuilder::read($notification, $notificationCheck);
                } else {
                    break;
                }
            }

            $systemNotificationSuccessModel = new NotificationsSuccessModel(
                $systemNotifications,
                $systemNotificationQuery->getPage(),
                $systemNotificationQuery->getLimit(),
                ceil(count($userSystemNotifications) / $systemNotificationQuery->getLimit()),
                $notificationRepository->getUserActiveNotifications($user)
            );

            return ResponseTool::getResponse($systemNotificationSuccessModel);
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param Request $request
     * @param RequestServiceInterface $requestServiceInterface
     * @param NotificationRepository $notificationRepository
     * @param LoggerInterface $endpointLogger
     * @param TranslateService $translateService
     * @param NotificationCheckRepository $checkRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/notification/activate", name: "notificationActivate", methods: ["PUT"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator", "User"])]
    #[OA\Post(
        description: "Method get is activating given notification so user can see if he read this notification",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: SystemNotificationActivateQuery::class),
                type: "object"
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function notificationActivate(
        AuthorizedUserServiceInterface $authorizedUserService,
        Request                        $request,
        RequestServiceInterface        $requestServiceInterface,
        NotificationRepository         $notificationRepository,
        LoggerInterface                $endpointLogger,
        TranslateService               $translateService,
        NotificationCheckRepository    $checkRepository
    ): Response
    {
        $systemNotificationActivateQuery = $requestServiceInterface->getRequestBodyContent($request, SystemNotificationActivateQuery::class);

        if ($systemNotificationActivateQuery instanceof SystemNotificationActivateQuery) {

            $notification = $notificationRepository->findOneBy([
                "id" => $systemNotificationActivateQuery->getNotificationId()
            ]);

            if ($notification == null) {
                throw new DataNotFoundException([$translateService->getTranslation("NotificationDontExists")]);
            }

            $user = $authorizedUserService->getAuthorizedUser();

            $notificationCheck = $checkRepository->findOneBy([
                "user" => $user->getId(),
                "notification" => $notification->getId()
            ]);

            if (!$notificationCheck) {
                $notificationCheck = new NotificationCheck($user, $notification);
                $checkRepository->add($notificationCheck);
            }

            return ResponseTool::getResponse(null, 201);
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }
}