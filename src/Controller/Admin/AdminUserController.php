<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Annotation\AuthValidation;
use App\Builder\NotificationBuilder;
use App\Entity\UserDelete;
use App\Enums\Cache\AdminCacheKeys;
use App\Enums\Cache\AdminStockCacheTags;
use App\Enums\Cache\CacheValidTime;
use App\Enums\Cache\UserStockCacheTags;
use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Enums\UserRoles;
use App\Enums\UserRolesNames;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\Admin\AdminSystemRoleModel;
use App\Model\Admin\AdminUserBanModel;
use App\Model\Admin\AdminUserDeleteListSuccessModel;
use App\Model\Admin\AdminUserDeleteModel;
use App\Model\Admin\AdminUserModel;
use App\Model\Admin\AdminUsersSuccessModel;
use App\Model\Admin\AdminUserSystemRolesSuccessModel;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Model\Serialization\AdminAudiobooksSearchModel;
use App\Model\Serialization\AdminUsersSearchModel;
use App\Query\Admin\AdminUserActivateQuery;
use App\Query\Admin\AdminUserBanQuery;
use App\Query\Admin\AdminUserChangePasswordQuery;
use App\Query\Admin\AdminUserChangePhoneQuery;
use App\Query\Admin\AdminUserDeleteAcceptQuery;
use App\Query\Admin\AdminUserDeleteDeclineQuery;
use App\Query\Admin\AdminUserDeleteListQuery;
use App\Query\Admin\AdminUserDeleteQuery;
use App\Query\Admin\AdminUserRoleAddQuery;
use App\Query\Admin\AdminUserRoleRemoveQuery;
use App\Query\Admin\AdminUsersQuery;
use App\Repository\NotificationRepository;
use App\Repository\RoleRepository;
use App\Repository\UserBanHistoryRepository;
use App\Repository\UserDeleteRepository;
use App\Repository\UserInformationRepository;
use App\Repository\UserPasswordRepository;
use App\Repository\UserRepository;
use App\Service\RequestServiceInterface;
use App\Service\TranslateServiceInterface;
use App\Tool\ResponseTool;
use App\ValueGenerator\PasswordHashGenerator;
use DateTime;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
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
#[OA\Tag(name: 'AdminUser')]
#[Route('/api/admin')]
class AdminUserController extends AbstractController
{
    #[Route('/user/system/roles', name: 'adminUserSystemRoles', methods: ['GET'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Get(
        description: 'Endpoint is returning roles in system',
        requestBody: new OA\RequestBody(),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: AdminUserSystemRolesSuccessModel::class),
            ),
        ]
    )]
    public function adminUserSystemRoles(
        RoleRepository $roleRepository,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $successModel = $stockCache->get(AdminCacheKeys::ADMIN_ROLES->value, function (ItemInterface $item) use ($roleRepository) {
            $item->expiresAfter(CacheValidTime::DAY->value);
            $item->tag(AdminStockCacheTags::ADMIN_ROLES->value);

            $roles = $roleRepository->getSystemRoles();

            $successModel = new AdminUserSystemRolesSuccessModel();

            foreach ($roles as $role) {
                switch ($role->getName()) {
                    case UserRolesNames::GUEST->value:
                        $successModel->addRole(new AdminSystemRoleModel($role->getName(), UserRoles::GUEST->value));
                        break;
                    case UserRolesNames::USER->value:
                        $successModel->addRole(new AdminSystemRoleModel($role->getName(), UserRoles::USER->value));
                        break;
                }
            }

            return $successModel;
        });

        return ResponseTool::getResponse($successModel);
    }

    #[Route('/user/role/add', name: 'adminUserRoleAdd', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Patch(
        description: 'Endpoint is Adding role to user',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminUserRoleAddQuery::class),
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
    public function adminUserRoleAdd(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        TranslateServiceInterface $translateService,
    ): Response {
        $adminUserRoleAddQuery = $requestService->getRequestBodyContent($request, AdminUserRoleAddQuery::class);

        if ($adminUserRoleAddQuery instanceof AdminUserRoleAddQuery) {
            $user = $userRepository->find($adminUserRoleAddQuery->getUserId());

            if ($user === null) {
                $endpointLogger->error('User dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserDontExists')]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error('User is admin');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserDontExists')]);
            }

            switch ($adminUserRoleAddQuery->getRole()) {
                case UserRoles::GUEST:
                    $role = $roleRepository->findOneBy([
                        'name' => UserRolesNames::GUEST,
                    ]);
                    break;

                case UserRoles::USER:
                    $role = $roleRepository->findOneBy([
                        'name' => UserRolesNames::USER,
                    ]);
                    break;
            }

            $user->addRole($role);

            $userRepository->add($user);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/user/role/remove', name: 'adminUserRoleRemove', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Patch(
        description: 'Endpoint is removing role for user',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminUserRoleRemoveQuery::class),
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
    public function adminUserRoleRemove(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        TranslateServiceInterface $translateService,
    ): Response {
        $adminUserRoleRemoveQuery = $requestService->getRequestBodyContent($request, AdminUserRoleRemoveQuery::class);

        if ($adminUserRoleRemoveQuery instanceof AdminUserRoleRemoveQuery) {
            $user = $userRepository->find($adminUserRoleRemoveQuery->getUserId());

            if ($user === null) {
                $endpointLogger->error('User dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserDontExists')]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error('User is admin');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserDontExists')]);
            }

            switch ($adminUserRoleRemoveQuery->getRole()) {
                case UserRoles::GUEST:
                    $role = $roleRepository->findOneBy([
                        'name' => UserRolesNames::GUEST,
                    ]);
                    break;
                case UserRoles::USER:
                    $role = $roleRepository->findOneBy([
                        'name' => UserRolesNames::USER,
                    ]);
                    break;
            }

            $user->removeRole($role);

            $userRepository->add($user);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/user/activate', name: 'adminUserActivate', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Patch(
        description: 'Endpoint is activating given user',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminUserActivateQuery::class),
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
    public function adminUserActivate(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        TranslateServiceInterface $translateService,
    ): Response {
        $adminUserActivateQuery = $requestService->getRequestBodyContent($request, AdminUserActivateQuery::class);

        if ($adminUserActivateQuery instanceof AdminUserActivateQuery) {
            $user = $userRepository->find($adminUserActivateQuery->getUserId());

            if ($user === null) {
                $endpointLogger->error('User dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserDontExists')]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error('User is admin');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserDontExists')]);
            }

            $userRole = $roleRepository->findOneBy([
                'name' => UserRolesNames::USER,
            ]);

            $user
                ->addRole($userRole)
                ->setActive(true);

            $userRepository->add($user);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/user/ban', name: 'adminUserBan', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR])]
    #[OA\Patch(
        description: 'Endpoint is banning/unbanning user',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminUserBanQuery::class),
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
    public function adminUserBan(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        UserRepository $userRepository,
        TranslateServiceInterface $translateService,
    ): Response {
        $adminUserBanQuery = $requestService->getRequestBodyContent($request, AdminUserBanQuery::class);

        if ($adminUserBanQuery instanceof AdminUserBanQuery) {
            $user = $userRepository->find($adminUserBanQuery->getUserId());

            if ($user === null) {
                $endpointLogger->error('User dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserDontExists')]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error('User is admin');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserDontExists')]);
            }

            $user->setBanned($adminUserBanQuery->isBanned());

            $userRepository->add($user);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/user/change/password', name: 'adminUserChangePassword', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Patch(
        description: 'Endpoint is changing password of given user',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminUserChangePasswordQuery::class),
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
    public function adminUserChangePassword(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        UserRepository $userRepository,
        UserPasswordRepository $userPasswordRepository,
        TranslateServiceInterface $translateService,
    ): Response {
        $adminUserChangePasswordQuery = $requestService->getRequestBodyContent($request, AdminUserChangePasswordQuery::class);

        if ($adminUserChangePasswordQuery instanceof AdminUserChangePasswordQuery) {
            $user = $userRepository->find($adminUserChangePasswordQuery->getUserId());

            if ($user === null) {
                $endpointLogger->error('User dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserDontExists')]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error('User is admin');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserDontExists')]);
            }
            $userPassword = $userPasswordRepository->findOneBy([
                'user' => $user->getId(),
            ]);

            $passwordGenerator = new PasswordHashGenerator($adminUserChangePasswordQuery->getNewPassword());

            $userPassword->setPassword($passwordGenerator);

            $userPasswordRepository->add($userPassword);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/user/change/phone', name: 'adminUserChangePhone', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Patch(
        description: 'Endpoint is changing phone number of given user',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminUserChangePhoneQuery::class),
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
    public function adminUserChangePhone(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        UserRepository $userRepository,
        UserInformationRepository $userInformationRepository,
        TranslateServiceInterface $translateService,
    ): Response {
        $adminUserChangePhoneQuery = $requestService->getRequestBodyContent($request, AdminUserChangePhoneQuery::class);

        if ($adminUserChangePhoneQuery instanceof AdminUserChangePhoneQuery) {
            $user = $userRepository->find($adminUserChangePhoneQuery->getUserId());

            if ($user === null) {
                $endpointLogger->error('User dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserDontExists')]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error('User is admin');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserDontExists')]);
            }

            $duplicatedNumber = $userInformationRepository->findOneBy([
                'phoneNumber' => $adminUserChangePhoneQuery->getNewPhone(),
            ]);

            if ($duplicatedNumber !== null) {
                $endpointLogger->error('User PhoneNumber Exists');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('PhoneNumberExists')]);
            }

            $userInfo = $user->getUserInformation();

            $userInfo->setPhoneNumber($adminUserChangePhoneQuery->getNewPhone());

            $userInformationRepository->add($userInfo);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/users', name: 'adminUsers', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Post(
        description: 'Endpoint is returning list of users in system',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminUsersQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: AdminUsersSuccessModel::class),
            ),
        ]
    )]
    public function adminUsers(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        UserRepository $userRepository,
        UserDeleteRepository $userDeleteRepository,
        UserBanHistoryRepository $banHistoryRepository,
        TranslateServiceInterface $translateService,
        SerializerInterface $serializer,
    ): Response {
        $adminUsersQuery = $requestService->getRequestBodyContent($request, AdminUsersQuery::class);

        if ($adminUsersQuery instanceof AdminUsersQuery) {
            $successModel = new AdminUsersSuccessModel();

            $usersSearchData = $adminUsersQuery->getSearchData();

            $userSearchModel = new AdminUsersSearchModel();
            $serializer->deserialize(
                json_encode($usersSearchData),
                AdminUsersSearchModel::class,
                'json',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE             => $userSearchModel,
                    AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
                ],
            );

            $minResult = $adminUsersQuery->getPage() * $adminUsersQuery->getLimit();
            $maxResult = $adminUsersQuery->getLimit() + $minResult;

            $allUsers = $userRepository->searchUsers($userSearchModel);

            foreach ($allUsers as $index => $user) {
                if ($index < $minResult) {
                    continue;
                }

                if ($userRepository->userIsAdmin($user)) {
                    ++$maxResult;
                } elseif ($index < $maxResult) {
                    $userDeleted = $userDeleteRepository->userInToDeleteList($user);

                    $userModel = new AdminUserModel(
                        (string)$user->getId(),
                        $user->isActive(),
                        $user->isBanned(),
                        $user->getUserInformation()->getEmail(),
                        $user->getUserInformation()->getFirstname(),
                        $user->getUserInformation()->getLastname(),
                        $user->getDateCreate(),
                        $userDeleted,
                    );

                    $userModel->setPhoneNumber($user->getUserInformation()->getPhoneNumber());

                    if ($user->isBanned()) {
                        $userBan = $banHistoryRepository->getActiveBan($user);

                        if ($userBan !== null) {
                            $userModel->setUserBan(new AdminUserBanModel($userBan->getDateFrom(), $userBan->getDateTo(), $userBan->getType()));
                        }
                    }

                    foreach ($user->getRoles() as $role) {
                        switch ($role->getName()) {
                            case UserRolesNames::GUEST->value:
                                $userModel->addRole(UserRoles::GUEST);
                                break;
                            case UserRolesNames::USER->value:
                                $userModel->addRole(UserRoles::USER);
                                break;
                        }
                    }

                    $successModel->addUser($userModel);
                } else {
                    break;
                }
            }

            $successModel->setPage($adminUsersQuery->getPage());
            $successModel->setLimit($adminUsersQuery->getLimit());

            $successModel->setMaxPage((int)ceil(count($allUsers) / $adminUsersQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/user/delete', name: 'adminUserDelete', methods: ['DELETE'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR])]
    #[OA\Delete(
        description: 'Endpoint is deleting given user',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminUserDeleteQuery::class),
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
    public function adminUserDelete(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        UserRepository $userRepository,
        UserDeleteRepository $userDeleteRepository,
        MailerInterface $mailer,
        TranslateServiceInterface $translateService,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $adminUserDeleteQuery = $requestService->getRequestBodyContent($request, AdminUserDeleteQuery::class);

        if ($adminUserDeleteQuery instanceof AdminUserDeleteQuery) {
            $user = $userRepository->find($adminUserDeleteQuery->getUserId());

            if ($user === null) {
                $endpointLogger->error('User dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserDontExists')]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error('User is admin');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserDontExists')]);
            }

            $userDelete = $userDeleteRepository->findOneBy([
                'user' => $user->getId(),
            ]);

            if ($userDelete === null) {
                $userDelete = new UserDelete($user);
            }

            $userDelete
                ->setDeleted(true)
                ->setDateDeleted(new DateTime());

            $userDeleteRepository->add($userDelete);

            $stockCache->invalidateTags([
                UserStockCacheTags::USER_DELETED->value,
            ]);

            if ($_ENV['APP_ENV'] !== 'test') {
                $email = (new TemplatedEmail())
                    ->from($_ENV['INSTITUTION_EMAIL'])
                    ->to($user->getUserInformation()->getEmail())
                    ->subject($translateService->getTranslation('AccountDeletedSubject'))
                    ->htmlTemplate('emails/userDeleted.html.twig')
                    ->context([
                        'userName' => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                        'lang'     => $request->getPreferredLanguage() !== null ? $request->getPreferredLanguage() : $translateService->getLocate(),
                    ]);
                $mailer->send($email);
            }

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/user/delete/list', name: 'adminUserDeleteList', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Post(
        description: 'Endpoint is returning list of users to delete',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminUserDeleteListQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: AdminUserDeleteListSuccessModel::class),
            ),
        ]
    )]
    public function adminUserDeleteList(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        UserRepository $userRepository,
        UserDeleteRepository $userDeleteRepository,
        TranslateServiceInterface $translateService,
    ): Response {
        $adminUserDeleteListQuery = $requestService->getRequestBodyContent($request, AdminUserDeleteListQuery::class);

        if ($adminUserDeleteListQuery instanceof AdminUserDeleteListQuery) {
            $successModel = new AdminUserDeleteListSuccessModel();

            $minResult = $adminUserDeleteListQuery->getPage() * $adminUserDeleteListQuery->getLimit();
            $maxResult = $adminUserDeleteListQuery->getLimit() + $minResult;

            $allDeleteUsers = $userDeleteRepository->findBy([
                'deleted' => true,
            ]);

            foreach ($allDeleteUsers as $index => $userDelete) {
                $user = $userDelete->getUser();

                if ($index < $minResult || $userRepository->userIsAdmin($user)) {
                    continue;
                }

                if ($index < $maxResult) {
                    $userDeleteModel = new AdminUserDeleteModel(
                        (string)$user->getId(),
                        $user->isActive(),
                        $user->isBanned(),
                        $user->getUserInformation()->getEmail(),
                        $user->getUserInformation()->getFirstname(),
                        $userDelete->getDeleted(),
                        $userDelete->getDeclined(),
                    );

                    if ($userDelete->getDateDeleted() !== null) {
                        $userDeleteModel->setDateDeleted($userDelete->getDateDeleted());
                    }

                    $successModel->addUser($userDeleteModel);
                } else {
                    break;
                }
            }

            $successModel->setPage($adminUserDeleteListQuery->getPage());
            $successModel->setLimit($adminUserDeleteListQuery->getLimit());

            $successModel->setMaxPage((int)ceil(count($allDeleteUsers) / $adminUserDeleteListQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/user/to/delete/list', name: 'adminUserToDeleteList', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Post(
        description: 'Endpoint is returning list of already delete users',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminUserDeleteListQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: AdminUserDeleteListSuccessModel::class),
            ),
        ]
    )]
    public function adminUserToDeleteList(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        UserRepository $userRepository,
        UserDeleteRepository $userDeleteRepository,
        TranslateServiceInterface $translateService,
    ): Response {
        $adminUserDeleteListQuery = $requestService->getRequestBodyContent($request, AdminUserDeleteListQuery::class);

        if ($adminUserDeleteListQuery instanceof AdminUserDeleteListQuery) {
            $successModel = new AdminUserDeleteListSuccessModel();

            $minResult = $adminUserDeleteListQuery->getPage() * $adminUserDeleteListQuery->getLimit();
            $maxResult = $adminUserDeleteListQuery->getLimit() + $minResult;

            $allDeleteUsers = $userDeleteRepository->getUsersToDelete();

            foreach ($allDeleteUsers as $index => $userDelete) {
                $user = $userDelete->getUser();

                if ($index < $minResult || $userRepository->userIsAdmin($user)) {
                    continue;
                }

                if ($index < $maxResult) {
                    $userDeleteModel = new AdminUserDeleteModel(
                        (string)$user->getId(),
                        $user->isActive(),
                        $user->isBanned(),
                        $user->getUserInformation()->getEmail(),
                        $user->getUserInformation()->getFirstname(),
                        $userDelete->getDeleted(),
                        $userDelete->getDeclined(),
                    );

                    if ($userDelete->getDateDeleted() !== null) {
                        $userDeleteModel->setDateDeleted($userDelete->getDateDeleted());
                    }

                    $successModel->addUser($userDeleteModel);
                } else {
                    break;
                }
            }

            $successModel->setPage($adminUserDeleteListQuery->getPage());
            $successModel->setLimit($adminUserDeleteListQuery->getLimit());

            $successModel->setMaxPage((int)ceil(count($allDeleteUsers) / $adminUserDeleteListQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/user/delete/accept', name: 'adminUserDeleteAccept', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Patch(
        description: 'Endpoint is deleting given user',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminUserDeleteAcceptQuery::class),
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
    public function adminUserDeleteAccept(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        UserDeleteRepository $userDeleteRepository,
        MailerInterface $mailer,
        TranslateServiceInterface $translateService,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $adminUserDeleteAcceptQuery = $requestService->getRequestBodyContent($request, AdminUserDeleteAcceptQuery::class);

        if ($adminUserDeleteAcceptQuery instanceof AdminUserDeleteAcceptQuery) {
            $userDelete = $userDeleteRepository->findOneBy([
                'user' => $adminUserDeleteAcceptQuery->getUserId(),
            ]);

            if ($userDelete === null) {
                $endpointLogger->error('User dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserDeleteDontExists')]);
            }

            $user = $userDelete->getUser();

            $userInDelete = $userDeleteRepository->userInList($user);

            if ($userInDelete) {
                $endpointLogger->error('User in list');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserDeleted')]);
            }

            $userDelete->setDeleted(true);
            $userDelete->setDateDeleted(new DateTime());

            $userDeleteRepository->add($userDelete);

            $stockCache->invalidateTags([
                UserStockCacheTags::USER_DELETED->value,
            ]);

            if ($_ENV['APP_ENV'] !== 'test') {
                $email = (new TemplatedEmail())
                    ->from($_ENV['INSTITUTION_EMAIL'])
                    ->to($user->getUserInformation()->getEmail())
                    ->subject($translateService->getTranslation('AccountDeletedSubject'))
                    ->htmlTemplate('emails/userDeleted.html.twig')
                    ->context([
                        'userName' => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                        'lang'     => $request->getPreferredLanguage() !== null ? $request->getPreferredLanguage() : $translateService->getLocate(),
                    ]);
                $mailer->send($email);
            }

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/user/delete/decline', name: 'adminUserDeleteDecline', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Patch(
        description: 'Endpoint is declining user request to delete his account',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminUserDeleteDeclineQuery::class),
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
    public function adminUserDeleteDecline(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        UserRepository $userRepository,
        UserDeleteRepository $userDeleteRepository,
        MailerInterface $mailer,
        NotificationRepository $notificationRepository,
        TranslateServiceInterface $translateService,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $adminUserDeleteDeclineQuery = $requestService->getRequestBodyContent($request, AdminUserDeleteDeclineQuery::class);

        if ($adminUserDeleteDeclineQuery instanceof AdminUserDeleteDeclineQuery) {
            $userDelete = $userDeleteRepository->findOneBy([
                'user' => $adminUserDeleteDeclineQuery->getUserId(),
            ]);

            if ($userDelete === null) {
                $endpointLogger->error('User dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserDeleteDontExists')]);
            }

            $user = $userDelete->getUser();

            $userInDelete = $userDeleteRepository->userInList($user);

            if ($userInDelete) {
                $endpointLogger->error('User in list');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserDeleted')]);
            }

            $userDelete->setDeclined(true);

            $userDeleteRepository->add($userDelete);

            $user->setActive(true);

            $userRepository->add($user);

            $stockCache->invalidateTags([
                UserStockCacheTags::USER_DELETED->value,
            ]);

            if ($_ENV['APP_ENV'] !== 'test') {
                $email = (new TemplatedEmail())
                    ->from($_ENV['INSTITUTION_EMAIL'])
                    ->to($user->getUserInformation()->getEmail())
                    ->subject($translateService->getTranslation('DeletionRejectedSubject'))
                    ->htmlTemplate('emails/userDeletedDecline.html.twig')
                    ->context([
                        'userName' => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                        'lang'     => $request->getPreferredLanguage() !== null ? $request->getPreferredLanguage() : $translateService->getLocate(),
                    ]);
                $mailer->send($email);
            }

            $notificationBuilder = new NotificationBuilder();

            $notification = $notificationBuilder
                ->setType(NotificationType::USER_DELETE_DECLINE)
                ->setAction($userDelete->getId())
                ->addUser($user)
                ->setUserAction(NotificationUserType::SYSTEM)
                ->setActive(true)
                ->build($stockCache);

            $notificationRepository->add($notification);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }
}
