<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Builder\NotificationBuilder;
use App\Exception\InvalidJsonDataException;
use App\Model\DataNotFoundModel;
use App\Model\JsonDataInvalidModel;
use App\Model\NotAuthorizeModel;
use App\Model\NotificationsSuccessModel;
use App\Model\PermissionNotGrantedModel;
use App\Query\SystemNotificationQuery;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Tool\ResponseTool;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * NotificationController
 */
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
     * @param UserRepository $userRepository
     * @param LoggerInterface $endpointLogger
     * @return Response
     *
     * @throws InvalidJsonDataException
     */
    #[Route("/api/notifications", name: "notifications", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
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
        UserRepository                 $userRepository,
        LoggerInterface                $endpointLogger
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
                } elseif ($index < $maxResult) {
                    $systemNotifications[] = NotificationBuilder::read($notification);
                } else {
                    break;
                }
            }

            $systemNotificationSuccessModel = new NotificationsSuccessModel(
                $systemNotifications,
                $systemNotificationQuery->getPage(),
                $systemNotificationQuery->getLimit(),
                ceil(count($userSystemNotifications) / $systemNotificationQuery->getLimit())
            );

            return ResponseTool::getResponse($systemNotificationSuccessModel);
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("notification.invalid.query");
        }
    }
}