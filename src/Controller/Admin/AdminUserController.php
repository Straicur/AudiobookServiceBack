<?php

declare(strict_types = 1);

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
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

use function count;

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
class AdminUserController extends AbstractController
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly TagAwareCacheInterface $stockCache,
        private readonly RequestServiceInterface $requestService,
        private readonly LoggerInterface $endpointLogger,
        private readonly UserRepository $userRepository,
        private readonly TranslateServiceInterface $translateService,
        private readonly UserPasswordRepository $userPasswordRepository,
        private readonly UserInformationRepository $userInformationRepository,
        private readonly UserDeleteRepository $userDeleteRepository,
        private readonly UserBanHistoryRepository $banHistoryRepository,
        private readonly SerializerInterface $serializer,
        private readonly MailerInterface $mailer,
        private readonly NotificationRepository $notificationRepository,
        #[Autowire(env: 'INSTITUTION_EMAIL')] private readonly string $institutionEmail
    ) {

    }

    #[Route('/api/admin/user/system/roles', name: 'adminUserSystemRoles', methods: ['GET'])]
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
    public function adminUserSystemRoles(): Response
    {
        $successModel = $this->stockCache->get(AdminCacheKeys::ADMIN_ROLES->value, function (ItemInterface $item): AdminUserSystemRolesSuccessModel {
            $item->expiresAfter(CacheValidTime::DAY->value);
            $item->tag(AdminStockCacheTags::ADMIN_ROLES->value);

            $roles = $this->roleRepository->getSystemRoles();
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

    #[Route('/api/admin/user/role/add', name: 'adminUserRoleAdd', methods: ['PATCH'])]
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
    ): Response {
        $adminUserRoleAddQuery = $this->requestService->getRequestBodyContent($request, AdminUserRoleAddQuery::class);

        if ($adminUserRoleAddQuery instanceof AdminUserRoleAddQuery) {
            $user = $this->userRepository->find($adminUserRoleAddQuery->getUserId());

            if (null === $user) {
                $this->endpointLogger->error('User dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserDontExists')]);
            }

            if ($this->userRepository->userIsAdmin($user)) {
                $this->endpointLogger->error('User is admin');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserDontExists')]);
            }

            switch ($adminUserRoleAddQuery->getRole()) {
                case UserRoles::GUEST:
                    $role = $this->roleRepository->findOneBy([
                        'name' => UserRolesNames::GUEST,
                    ]);
                    break;

                case UserRoles::USER:
                    $role = $this->roleRepository->findOneBy([
                        'name' => UserRolesNames::USER,
                    ]);
                    break;
            }

            $user->addRole($role);

            $this->userRepository->add($user);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/user/role/remove', name: 'adminUserRoleRemove', methods: ['PATCH'])]
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
    ): Response {
        $adminUserRoleRemoveQuery = $this->requestService->getRequestBodyContent($request, AdminUserRoleRemoveQuery::class);

        if ($adminUserRoleRemoveQuery instanceof AdminUserRoleRemoveQuery) {
            $user = $this->userRepository->find($adminUserRoleRemoveQuery->getUserId());

            if (null === $user) {
                $this->endpointLogger->error('User dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserDontExists')]);
            }

            if ($this->userRepository->userIsAdmin($user)) {
                $this->endpointLogger->error('User is admin');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserDontExists')]);
            }

            switch ($adminUserRoleRemoveQuery->getRole()) {
                case UserRoles::GUEST:
                    $role = $this->roleRepository->findOneBy([
                        'name' => UserRolesNames::GUEST,
                    ]);
                    break;
                case UserRoles::USER:
                    $role = $this->roleRepository->findOneBy([
                        'name' => UserRolesNames::USER,
                    ]);
                    break;
            }

            $user->removeRole($role);

            $this->userRepository->add($user);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/user/activate', name: 'adminUserActivate', methods: ['PATCH'])]
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
    ): Response {
        $adminUserActivateQuery = $this->requestService->getRequestBodyContent($request, AdminUserActivateQuery::class);

        if ($adminUserActivateQuery instanceof AdminUserActivateQuery) {
            $user = $this->userRepository->find($adminUserActivateQuery->getUserId());

            if (null === $user) {
                $this->endpointLogger->error('User dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserDontExists')]);
            }

            if ($this->userRepository->userIsAdmin($user)) {
                $this->endpointLogger->error('User is admin');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserDontExists')]);
            }

            $userRole = $this->roleRepository->findOneBy([
                'name' => UserRolesNames::USER,
            ]);

            $user
                ->addRole($userRole)
                ->setActive(true);

            $this->userRepository->add($user);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/user/ban', name: 'adminUserBan', methods: ['PATCH'])]
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
    ): Response {
        $adminUserBanQuery = $this->requestService->getRequestBodyContent($request, AdminUserBanQuery::class);

        if ($adminUserBanQuery instanceof AdminUserBanQuery) {
            $user = $this->userRepository->find($adminUserBanQuery->getUserId());

            if (null === $user) {
                $this->endpointLogger->error('User dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserDontExists')]);
            }

            if ($this->userRepository->userIsAdmin($user)) {
                $this->endpointLogger->error('User is admin');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserDontExists')]);
            }

            $user->setBanned($adminUserBanQuery->isBanned());

            $this->userRepository->add($user);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/user/change/password', name: 'adminUserChangePassword', methods: ['PATCH'])]
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
    ): Response {
        $adminUserChangePasswordQuery = $this->requestService->getRequestBodyContent($request, AdminUserChangePasswordQuery::class);

        if ($adminUserChangePasswordQuery instanceof AdminUserChangePasswordQuery) {
            $user = $this->userRepository->find($adminUserChangePasswordQuery->getUserId());

            if (null === $user) {
                $this->endpointLogger->error('User dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserDontExists')]);
            }

            if ($this->userRepository->userIsAdmin($user)) {
                $this->endpointLogger->error('User is admin');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserDontExists')]);
            }

            $userPassword = $this->userPasswordRepository->findOneBy([
                'user' => $user->getId(),
            ]);

            $passwordGenerator = new PasswordHashGenerator($adminUserChangePasswordQuery->getNewPassword());

            $userPassword->setPassword($passwordGenerator);

            $this->userPasswordRepository->add($userPassword);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/user/change/phone', name: 'adminUserChangePhone', methods: ['PATCH'])]
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
    ): Response {
        $adminUserChangePhoneQuery = $this->requestService->getRequestBodyContent($request, AdminUserChangePhoneQuery::class);

        if ($adminUserChangePhoneQuery instanceof AdminUserChangePhoneQuery) {
            $user = $this->userRepository->find($adminUserChangePhoneQuery->getUserId());

            if (null === $user) {
                $this->endpointLogger->error('User dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserDontExists')]);
            }

            if ($this->userRepository->userIsAdmin($user)) {
                $this->endpointLogger->error('User is admin');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserDontExists')]);
            }

            $duplicatedNumber = $this->userInformationRepository->findOneBy([
                'phoneNumber' => $adminUserChangePhoneQuery->getNewPhone(),
            ]);

            if (null !== $duplicatedNumber) {
                $this->endpointLogger->error('User PhoneNumber Exists');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('PhoneNumberExists')]);
            }

            $userInfo = $user->getUserInformation();

            $userInfo->setPhoneNumber($adminUserChangePhoneQuery->getNewPhone());

            $this->userInformationRepository->add($userInfo);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/users', name: 'adminUsers', methods: ['POST'])]
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
    ): Response {
        $adminUsersQuery = $this->requestService->getRequestBodyContent($request, AdminUsersQuery::class);

        if ($adminUsersQuery instanceof AdminUsersQuery) {
            $successModel = new AdminUsersSuccessModel();

            $usersSearchData = $adminUsersQuery->getSearchData();

            $userSearchModel = new AdminUsersSearchModel();
            $this->serializer->deserialize(
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

            $allUsers = $this->userRepository->searchUsers($userSearchModel);

            foreach ($allUsers as $index => $user) {
                if ($index < $minResult) {
                    continue;
                }

                if ($this->userRepository->userIsAdmin($user)) {
                    ++$maxResult;
                } elseif ($index < $maxResult) {
                    $userDeleted = $this->userDeleteRepository->userInToDeleteList($user);

                    $userModel = new AdminUserModel(
                        (string) $user->getId(),
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
                        $userBan = $this->banHistoryRepository->getActiveBan($user);

                        if (null !== $userBan) {
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

            $successModel->setMaxPage((int) ceil(count($allUsers) / $adminUsersQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/user/delete', name: 'adminUserDelete', methods: ['DELETE'])]
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
    ): Response {
        $adminUserDeleteQuery = $this->requestService->getRequestBodyContent($request, AdminUserDeleteQuery::class);

        if ($adminUserDeleteQuery instanceof AdminUserDeleteQuery) {
            $user = $this->userRepository->find($adminUserDeleteQuery->getUserId());

            if (null === $user) {
                $this->endpointLogger->error('User dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserDontExists')]);
            }

            if ($this->userRepository->userIsAdmin($user)) {
                $this->endpointLogger->error('User is admin');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserDontExists')]);
            }

            $userDelete = $this->userDeleteRepository->findOneBy([
                'user' => $user->getId(),
            ]);

            if (null === $userDelete) {
                $userDelete = new UserDelete($user);
            }

            $userDelete
                ->setDeleted(true)
                ->setDateDeleted(new DateTime());

            $this->userDeleteRepository->add($userDelete);

            $this->stockCache->invalidateTags([
                UserStockCacheTags::USER_DELETED->value,
            ]);

            if ('test' !== $_ENV['APP_ENV']) {
                $email = new TemplatedEmail()
                    ->from($this->institutionEmail)
                    ->to($user->getUserInformation()->getEmail())
                    ->subject($this->translateService->getTranslation('AccountDeletedSubject'))
                    ->htmlTemplate('emails/userDeleted.html.twig')
                    ->context([
                        'userName' => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                        'lang'     => $request->getPreferredLanguage() ?? $this->translateService->getLocate(),
                    ]);
                $this->mailer->send($email);
            }

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/user/delete/list', name: 'adminUserDeleteList', methods: ['POST'])]
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
    ): Response {
        $adminUserDeleteListQuery = $this->requestService->getRequestBodyContent($request, AdminUserDeleteListQuery::class);

        if ($adminUserDeleteListQuery instanceof AdminUserDeleteListQuery) {
            $successModel = new AdminUserDeleteListSuccessModel();

            $minResult = $adminUserDeleteListQuery->getPage() * $adminUserDeleteListQuery->getLimit();
            $maxResult = $adminUserDeleteListQuery->getLimit() + $minResult;

            $allDeleteUsers = $this->userDeleteRepository->findBy([
                'deleted' => true,
            ]);

            foreach ($allDeleteUsers as $index => $userDelete) {
                $user = $userDelete->getUser();
                if ($index < $minResult) {
                    continue;
                }

                if ($this->userRepository->userIsAdmin($user)) {
                    continue;
                }

                if ($index < $maxResult) {
                    $userDeleteModel = new AdminUserDeleteModel(
                        (string) $user->getId(),
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

            $successModel->setMaxPage((int) ceil(count($allDeleteUsers) / $adminUserDeleteListQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/user/to/delete/list', name: 'adminUserToDeleteList', methods: ['POST'])]
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
    ): Response {
        $adminUserDeleteListQuery = $this->requestService->getRequestBodyContent($request, AdminUserDeleteListQuery::class);

        if ($adminUserDeleteListQuery instanceof AdminUserDeleteListQuery) {
            $successModel = new AdminUserDeleteListSuccessModel();

            $minResult = $adminUserDeleteListQuery->getPage() * $adminUserDeleteListQuery->getLimit();
            $maxResult = $adminUserDeleteListQuery->getLimit() + $minResult;

            $allDeleteUsers = $this->userDeleteRepository->getUsersToDelete();

            foreach ($allDeleteUsers as $index => $userDelete) {
                $user = $userDelete->getUser();
                if ($index < $minResult) {
                    continue;
                }

                if ($this->userRepository->userIsAdmin($user)) {
                    continue;
                }

                if ($index < $maxResult) {
                    $userDeleteModel = new AdminUserDeleteModel(
                        (string) $user->getId(),
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

            $successModel->setMaxPage((int) ceil(count($allDeleteUsers) / $adminUserDeleteListQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/user/delete/accept', name: 'adminUserDeleteAccept', methods: ['PATCH'])]
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
    ): Response {
        $adminUserDeleteAcceptQuery = $this->requestService->getRequestBodyContent($request, AdminUserDeleteAcceptQuery::class);

        if ($adminUserDeleteAcceptQuery instanceof AdminUserDeleteAcceptQuery) {
            $userDelete = $this->userDeleteRepository->findOneBy([
                'user' => $adminUserDeleteAcceptQuery->getUserId(),
            ]);

            if (null === $userDelete) {
                $this->endpointLogger->error('User dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserDeleteDontExists')]);
            }

            $user = $userDelete->getUser();

            $userInDelete = $this->userDeleteRepository->userInList($user);

            if ($userInDelete) {
                $this->endpointLogger->error('User in list');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserDeleted')]);
            }

            $userDelete->setDeleted(true);
            $userDelete->setDateDeleted(new DateTime());

            $this->userDeleteRepository->add($userDelete);

            $this->stockCache->invalidateTags([
                UserStockCacheTags::USER_DELETED->value,
            ]);

            if ('test' !== $_ENV['APP_ENV']) {
                $email = new TemplatedEmail()
                    ->from($this->institutionEmail)
                    ->to($user->getUserInformation()->getEmail())
                    ->subject($this->translateService->getTranslation('AccountDeletedSubject'))
                    ->htmlTemplate('emails/userDeleted.html.twig')
                    ->context([
                        'userName' => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                        'lang'     => $request->getPreferredLanguage() ?? $this->translateService->getLocate(),
                    ]);
                $this->mailer->send($email);
            }

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/user/delete/decline', name: 'adminUserDeleteDecline', methods: ['PATCH'])]
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
    ): Response {
        $adminUserDeleteDeclineQuery = $this->requestService->getRequestBodyContent($request, AdminUserDeleteDeclineQuery::class);

        if ($adminUserDeleteDeclineQuery instanceof AdminUserDeleteDeclineQuery) {
            $userDelete = $this->userDeleteRepository->findOneBy([
                'user' => $adminUserDeleteDeclineQuery->getUserId(),
            ]);

            if (null === $userDelete) {
                $this->endpointLogger->error('User dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserDeleteDontExists')]);
            }

            $user = $userDelete->getUser();

            $userInDelete = $this->userDeleteRepository->userInList($user);

            if ($userInDelete) {
                $this->endpointLogger->error('User in list');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserDeleted')]);
            }

            $userDelete->setDeclined(true);

            $this->userDeleteRepository->add($userDelete);

            $user->setActive(true);

            $this->userRepository->add($user);

            $this->stockCache->invalidateTags([
                UserStockCacheTags::USER_DELETED->value,
            ]);

            if ('test' !== $_ENV['APP_ENV']) {
                $email = new TemplatedEmail()
                    ->from($this->institutionEmail)
                    ->to($user->getUserInformation()->getEmail())
                    ->subject($this->translateService->getTranslation('DeletionRejectedSubject'))
                    ->htmlTemplate('emails/userDeletedDecline.html.twig')
                    ->context([
                        'userName' => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                        'lang'     => $request->getPreferredLanguage() ?? $this->translateService->getLocate(),
                    ]);
                $this->mailer->send($email);
            }

            $notificationBuilder = new NotificationBuilder();

            $notification = $notificationBuilder
                ->setType(NotificationType::USER_DELETE_DECLINE)
                ->setAction($userDelete->getId())
                ->addUser($user)
                ->setUserAction(NotificationUserType::SYSTEM)
                ->setActive(true)
                ->build($this->stockCache);

            $this->notificationRepository->add($notification);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }
}
