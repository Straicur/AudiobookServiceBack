<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Annotation\AuthValidation;
use App\Builder\NotificationBuilder;
use App\Enums\Cache\UserStockCacheTags;
use App\Enums\UserRolesNames;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\Admin\AdminUserNotificationsSuccessModel;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Model\Serialization\AdminNotificationsSearchModel;
use App\Query\Admin\AdminUserNotificationDeleteQuery;
use App\Query\Admin\AdminUserNotificationPatchQuery;
use App\Query\Admin\AdminUserNotificationPutQuery;
use App\Query\Admin\AdminUserNotificationsQuery;
use App\Repository\NotificationRepository;
use App\Service\Admin\Notification\AdminNotificationAddServiceInterface;
use App\Service\Admin\Notification\AdminNotificationPatchServiceInterface;
use App\Service\RequestServiceInterface;
use App\Service\TranslateServiceInterface;
use App\Tool\ResponseTool;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[OA\Response(
    response   : 400,
    description: 'JSON Data Invalid',
    content    : new Model(type: JsonDataInvalidModel::class)
)]
#[OA\Response(
    response   : 404,
    description: 'Data not found',
    content    : new Model(type: DataNotFoundModel::class)
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
#[OA\Tag(name: 'AdminUserNotifications')]
#[Route('/api/admin')]
class AdminUserNotificationsController extends AbstractController
{
    #[Route('/user/notifications', name: 'adminUserNotifications', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Post(
        description: 'Endpoint is returning list of notifications in system',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminUserNotificationsQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: AdminUserNotificationsSuccessModel::class),
            ),
        ]
    )]
    public function adminUserNotifications(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        NotificationRepository $notificationRepository,
        TranslateServiceInterface $translateService,
        SerializerInterface $serializer,
    ): Response {
        $adminUserNotificationsQuery = $requestService->getRequestBodyContent($request, AdminUserNotificationsQuery::class);

        if ($adminUserNotificationsQuery instanceof AdminUserNotificationsQuery) {
            $notificationSearchData = $adminUserNotificationsQuery->getSearchData();

            $notificationsSearchModel = new AdminNotificationsSearchModel();
            $serializer->deserialize(
                json_encode($notificationSearchData),
                AdminNotificationsSearchModel::class,
                'json',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE             => $notificationsSearchModel,
                    AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
                ],
            );

            $allUserSystemNotifications = $notificationRepository->getSearchNotifications($notificationsSearchModel);

            $systemNotifications = [];

            $minResult = $adminUserNotificationsQuery->getPage() * $adminUserNotificationsQuery->getLimit();
            $maxResult = $adminUserNotificationsQuery->getLimit() + $minResult;

            foreach ($allUserSystemNotifications as $index => $notification) {
                if ($index < $minResult) {
                    continue;
                }

                if ($index < $maxResult) {
                    $systemNotifications[] = NotificationBuilder::read($notification);
                } else {
                    break;
                }
            }

            $systemNotificationSuccessModel = new AdminUserNotificationsSuccessModel(
                $systemNotifications,
                $adminUserNotificationsQuery->getPage(),
                $adminUserNotificationsQuery->getLimit(),
                (int)ceil(count($allUserSystemNotifications) / $adminUserNotificationsQuery->getLimit()),
            );

            return ResponseTool::getResponse($systemNotificationSuccessModel);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/user/notification', name: 'adminUserNotificationPut', methods: ['PUT'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Put(
        description: 'Endpoint is adding notification',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminUserNotificationPutQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 201,
                description: 'Success',
            ),
        ]
    )]
    public function adminUserNotificationPut(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        AdminNotificationAddServiceInterface $adminNotificationAddService,
        TranslateServiceInterface $translateService,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $adminUserNotificationPutQuery = $requestService->getRequestBodyContent($request, AdminUserNotificationPutQuery::class);

        if ($adminUserNotificationPutQuery instanceof AdminUserNotificationPutQuery) {
            $adminNotificationAddService
                ->setData($adminUserNotificationPutQuery, $request)
                ->addNotification();

            $stockCache->invalidateTags([UserStockCacheTags::USER_NOTIFICATIONS->value]);

            return ResponseTool::getResponse(httpCode: 201);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/user/notification', name: 'adminUserNotificationPatch', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Patch(
        description: 'Endpoint is editing notification',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminUserNotificationPatchQuery::class),
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
    public function adminUserNotificationPatch(
        Request $request,
        RequestServiceInterface $requestService,
        AdminNotificationPatchServiceInterface $adminNotificationPatchService,
        LoggerInterface $endpointLogger,
        TranslateServiceInterface $translateService,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $adminUserNotificationPatchQuery = $requestService->getRequestBodyContent($request, AdminUserNotificationPatchQuery::class);

        if ($adminUserNotificationPatchQuery instanceof AdminUserNotificationPatchQuery) {
            $adminNotificationPatchService
                ->setData($adminUserNotificationPatchQuery, $request)
                ->editNotification();

            $stockCache->invalidateTags([UserStockCacheTags::USER_NOTIFICATIONS->value]);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/user/notification/delete', name: 'adminUserNotificationDelete', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Patch(
        description: 'Endpoint is deleting notification',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminUserNotificationDeleteQuery::class),
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
    public function adminUserNotificationDelete(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        NotificationRepository $notificationRepository,
        TranslateServiceInterface $translateService,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $adminUserNotificationDeleteQuery = $requestService->getRequestBodyContent($request, AdminUserNotificationDeleteQuery::class);

        if ($adminUserNotificationDeleteQuery instanceof AdminUserNotificationDeleteQuery) {
            $notification = $notificationRepository->find($adminUserNotificationDeleteQuery->getNotificationId());

            if ($notification === null) {
                $endpointLogger->error('Notification dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('NotificationDontExists')]);
            }

            $notification->setDeleted($adminUserNotificationDeleteQuery->isDelete());

            $notificationRepository->add($notification);

            $stockCache->invalidateTags([UserStockCacheTags::USER_NOTIFICATIONS->value]);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }
}
