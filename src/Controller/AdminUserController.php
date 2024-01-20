<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Builder\NotificationBuilder;
use App\Entity\UserDelete;
use App\Enums\CacheKeys;
use App\Enums\CacheValidTime;
use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Enums\StockCacheTags;
use App\Enums\UserRoles;
use App\Enums\UserRolesNames;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Exception\NotificationException;
use App\Model\Admin\AdminSystemRoleModel;
use App\Model\Admin\AdminUserDeleteListSuccessModel;
use App\Model\Admin\AdminUserDeleteModel;
use App\Model\Admin\AdminUserModel;
use App\Model\Admin\AdminUserNotificationsSuccessModel;
use App\Model\Admin\AdminUsersSuccessModel;
use App\Model\Admin\AdminUserSystemRolesSuccessModel;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Query\Admin\AdminUserActivateQuery;
use App\Query\Admin\AdminUserBanQuery;
use App\Query\Admin\AdminUserChangePasswordQuery;
use App\Query\Admin\AdminUserChangePhoneQuery;
use App\Query\Admin\AdminUserDeleteAcceptQuery;
use App\Query\Admin\AdminUserDeleteDeclineQuery;
use App\Query\Admin\AdminUserDeleteListQuery;
use App\Query\Admin\AdminUserDeleteQuery;
use App\Query\Admin\AdminUserNotificationDeleteQuery;
use App\Query\Admin\AdminUserNotificationPatchQuery;
use App\Query\Admin\AdminUserNotificationPutQuery;
use App\Query\Admin\AdminUserNotificationsQuery;
use App\Query\Admin\AdminUserRoleAddQuery;
use App\Query\Admin\AdminUserRoleRemoveQuery;
use App\Query\Admin\AdminUsersQuery;
use App\Repository\AudiobookCategoryRepository;
use App\Repository\AudiobookRepository;
use App\Repository\InstitutionRepository;
use App\Repository\NotificationRepository;
use App\Repository\RoleRepository;
use App\Repository\UserDeleteRepository;
use App\Repository\UserInformationRepository;
use App\Repository\UserPasswordRepository;
use App\Repository\UserRepository;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Service\TranslateService;
use App\Tool\ResponseTool;
use App\ValueGenerator\PasswordHashGenerator;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[OA\Response(
    response: 400,
    description: "JSON Data Invalid",
    content: new Model(type: JsonDataInvalidModel::class)
)]
#[OA\Response(
    response: 404,
    description: "Data not found",
    content: new Model(type: DataNotFoundModel::class)
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
#[OA\Tag(name: "AdminUser")]
class AdminUserController extends AbstractController
{
    /**
     * @param RoleRepository $roleRepository
     * @param TagAwareCacheInterface $stockCache
     * @return Response
     * @throws InvalidArgumentException
     */
    #[Route("/api/admin/user/system/roles", name: "adminUserSystemRoles", methods: ["GET"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Get(
        description: "Endpoint is returning roles in system",
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AdminUserSystemRolesSuccessModel::class)
            )
        ]
    )]
    public function adminUserSystemRoles(
        RoleRepository         $roleRepository,
        TagAwareCacheInterface $stockCache
    ): Response
    {
        $successModel = $stockCache->get(CacheKeys::ADMIN_ROLES->value, function (ItemInterface $item) use ($roleRepository) {
            $item->expiresAfter(CacheValidTime::DAY->value);
            $item->tag(StockCacheTags::ADMIN_ROLES->value);

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

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @param RoleRepository $roleRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/user/role/add", name: "adminUserRoleAdd", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Patch(
        description: "Endpoint is Adding role to user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminUserRoleAddQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function adminUserRoleAdd(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        UserRepository                 $userRepository,
        RoleRepository                 $roleRepository,
        TranslateService               $translateService
    ): Response
    {
        $adminUserRoleAddQuery = $requestService->getRequestBodyContent($request, AdminUserRoleAddQuery::class);

        if ($adminUserRoleAddQuery instanceof AdminUserRoleAddQuery) {

            $user = $userRepository->findOneBy([
                "id" => $adminUserRoleAddQuery->getUserId()
            ]);

            if ($user === null) {
                $endpointLogger->error("User dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserDontExists")]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error("User is admin");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserDontExists")]);
            }

            switch ($adminUserRoleAddQuery->getRole()) {
                case UserRoles::GUEST:
                    $guestRole = $roleRepository->findOneBy([
                        "name" => UserRolesNames::GUEST
                    ]);
                    $user->addRole($guestRole);
                    break;

                case UserRoles::USER:
                    $userRole = $roleRepository->findOneBy([
                        "name" => UserRolesNames::USER
                    ]);
                    $user->addRole($userRole);
                    break;

                case UserRoles::ADMINISTRATOR:
                    $adminRole = $roleRepository->findOneBy([
                        "name" => "Administrator"
                    ]);
                    $user->addRole($adminRole);
                    break;

            }

            $userRepository->add($user);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @param RoleRepository $roleRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/user/role/remove", name: "adminUserRoleRemove", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Patch(
        description: "Endpoint is removing role for user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminUserRoleRemoveQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function adminUserRoleRemove(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        UserRepository                 $userRepository,
        RoleRepository                 $roleRepository,
        TranslateService               $translateService
    ): Response
    {
        $adminUserRoleRemoveQuery = $requestService->getRequestBodyContent($request, AdminUserRoleRemoveQuery::class);

        if ($adminUserRoleRemoveQuery instanceof AdminUserRoleRemoveQuery) {

            $user = $userRepository->findOneBy([
                "id" => $adminUserRoleRemoveQuery->getUserId()
            ]);

            if ($user === null) {
                $endpointLogger->error("User dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserDontExists")]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error("User is admin");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserDontExists")]);
            }

            switch ($adminUserRoleRemoveQuery->getRole()) {
                case UserRoles::GUEST:
                    $guestRole = $roleRepository->findOneBy([
                        "name" => UserRolesNames::GUEST
                    ]);
                    $user->removeRole($guestRole);
                    break;

                case UserRoles::USER:
                    $userRole = $roleRepository->findOneBy([
                        "name" => UserRolesNames::USER
                    ]);
                    $user->removeRole($userRole);
                    break;

                case UserRoles::ADMINISTRATOR:
                    $adminRole = $roleRepository->findOneBy([
                        "name" => "Administrator"
                    ]);
                    $user->removeRole($adminRole);
                    break;

            }

            $userRepository->add($user);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @param RoleRepository $roleRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/user/activate", name: "adminUserActivate", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Patch(
        description: "Endpoint is activating given user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminUserActivateQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function adminUserActivate(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        UserRepository                 $userRepository,
        RoleRepository                 $roleRepository,
        TranslateService               $translateService
    ): Response
    {
        $adminUserActivateQuery = $requestService->getRequestBodyContent($request, AdminUserActivateQuery::class);

        if ($adminUserActivateQuery instanceof AdminUserActivateQuery) {

            $user = $userRepository->findOneBy([
                "id" => $adminUserActivateQuery->getUserId()
            ]);

            if ($user === null) {
                $endpointLogger->error("User dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserDontExists")]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error("User is admin");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserDontExists")]);
            }

            $userRole = $roleRepository->findOneBy([
                "name" => UserRolesNames::USER
            ]);

            $user->addRole($userRole);
            $user->setActive(true);

            $userRepository->add($user);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/user/ban", name: "adminUserBan", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Patch(
        description: "Endpoint is banning/unbanning user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminUserBanQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function adminUserBan(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        UserRepository                 $userRepository,
        TranslateService               $translateService
    ): Response
    {
        $adminUserBanQuery = $requestService->getRequestBodyContent($request, AdminUserBanQuery::class);

        if ($adminUserBanQuery instanceof AdminUserBanQuery) {

            $user = $userRepository->findOneBy([
                "id" => $adminUserBanQuery->getUserId()
            ]);

            if ($user === null) {
                $endpointLogger->error("User dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserDontExists")]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error("User is admin");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserDontExists")]);
            }

            $user->setBanned($adminUserBanQuery->isBanned());

            $userRepository->add($user);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @param UserPasswordRepository $userPasswordRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/user/change/password", name: "adminUserChangePassword", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Patch(
        description: "Endpoint is changing password of given user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminUserChangePasswordQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function adminUserChangePassword(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        UserRepository                 $userRepository,
        UserPasswordRepository         $userPasswordRepository,
        TranslateService               $translateService
    ): Response
    {
        $adminUserChangePasswordQuery = $requestService->getRequestBodyContent($request, AdminUserChangePasswordQuery::class);

        if ($adminUserChangePasswordQuery instanceof AdminUserChangePasswordQuery) {

            $user = $userRepository->findOneBy([
                "id" => $adminUserChangePasswordQuery->getUserId()
            ]);

            if ($user === null) {
                $endpointLogger->error("User dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserDontExists")]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error("User is admin");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserDontExists")]);
            }
            $userPassword = $userPasswordRepository->findOneBy([
                "user" => $user->getId()
            ]);

            $passwordGenerator = new PasswordHashGenerator($adminUserChangePasswordQuery->getNewPassword());

            $userPassword->setPassword($passwordGenerator);

            $userPasswordRepository->add($userPassword);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @param UserInformationRepository $userInformationRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/user/change/phone", name: "adminUserChangePhone", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Patch(
        description: "Endpoint is changing phone number of given user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminUserChangePhoneQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function adminUserChangePhone(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        UserRepository                 $userRepository,
        UserInformationRepository      $userInformationRepository,
        TranslateService               $translateService
    ): Response
    {
        $adminUserChangePhoneQuery = $requestService->getRequestBodyContent($request, AdminUserChangePhoneQuery::class);

        if ($adminUserChangePhoneQuery instanceof AdminUserChangePhoneQuery) {

            $user = $userRepository->findOneBy([
                "id" => $adminUserChangePhoneQuery->getUserId()
            ]);

            if ($user === null) {
                $endpointLogger->error("User dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserDontExists")]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error("User is admin");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserDontExists")]);
            }
            $duplicatedNumber = $userInformationRepository->findOneBy([
                'phoneNumber' => $adminUserChangePhoneQuery->getNewPhone()
            ]);

            if ($duplicatedNumber !== null) {
                $endpointLogger->error("User PhoneNumber Exists");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("PhoneNumberExists")]);
            }

            $userInfo = $user->getUserInformation();

            $userInfo->setPhoneNumber($adminUserChangePhoneQuery->getNewPhone());

            $userInformationRepository->add($userInfo);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @param UserDeleteRepository $userDeleteRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/users", name: "adminUsers", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is returning list of users in system",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminUsersQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AdminUsersSuccessModel::class)
            )
        ]
    )]
    public function adminUsers(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        UserRepository                 $userRepository,
        UserDeleteRepository           $userDeleteRepository,
        TranslateService               $translateService
    ): Response
    {
        $adminUsersQuery = $requestService->getRequestBodyContent($request, AdminUsersQuery::class);

        if ($adminUsersQuery instanceof AdminUsersQuery) {

            $successModel = new AdminUsersSuccessModel();

            $usersSearchData = $adminUsersQuery->getSearchData();

            $email = null;
            $phoneNumber = null;
            $firstname = null;
            $lastname = null;
            $active = null;
            $banned = null;
            $order = null;

            if (array_key_exists('email', $usersSearchData)) {
                $email = ($usersSearchData['email'] && '' !== $usersSearchData['email']) ? "%" . $usersSearchData['email'] . "%" : null;
            }
            if (array_key_exists('phoneNumber', $usersSearchData)) {
                $phoneNumber = ($usersSearchData['phoneNumber'] && '' !== $usersSearchData['phoneNumber']) ? "%" . $usersSearchData['phoneNumber'] . "%" : null;
            }
            if (array_key_exists('firstname', $usersSearchData)) {
                $firstname = ($usersSearchData['firstname'] && '' !== $usersSearchData['firstname']) ? "%" . $usersSearchData['firstname'] . "%" : null;
            }
            if (array_key_exists('lastname', $usersSearchData)) {
                $lastname = ($usersSearchData['lastname'] && '' !== $usersSearchData['lastname']) ? "%" . $usersSearchData['lastname'] . "%" : null;
            }
            if (array_key_exists('active', $usersSearchData)) {
                $active = $usersSearchData['active'];
            }
            if (array_key_exists('banned', $usersSearchData)) {
                $banned = $usersSearchData['banned'];
            }
            if (array_key_exists('order', $usersSearchData)) {
                $order = $usersSearchData['order'];
            }

            $minResult = $adminUsersQuery->getPage() * $adminUsersQuery->getLimit();
            $maxResult = $adminUsersQuery->getLimit() + $minResult;

            $allUsers = $userRepository->searchUsers($email, $phoneNumber, $firstname, $lastname, $active, $banned, $order);

            foreach ($allUsers as $index => $user) {
                if ($index < $minResult) {
                    continue;
                }

                if ($userRepository->userIsAdmin($user)) {
                    ++$maxResult;
                } elseif ($index < $maxResult) {

                    $userDeleted = $userDeleteRepository->userInToDeleteList($user);

                    $userModel = new AdminUserModel(
                        $user->getId(),
                        $user->isActive(),
                        $user->isBanned(),
                        $user->getUserInformation()->getEmail(),
                        $user->getUserInformation()->getFirstname(),
                        $user->getUserInformation()->getLastname(),
                        $user->getDateCreate(),
                        $userDeleted
                    );

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

            $successModel->setMaxPage(ceil(count($allUsers) / $adminUsersQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @param UserDeleteRepository $userDeleteRepository
     * @param MailerInterface $mailer
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     * @throws TransportExceptionInterface
     */
    #[Route("/api/admin/user/delete", name: "adminUserDelete", methods: ["DELETE"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Delete(
        description: "Endpoint is deleting given user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminUserDeleteQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function adminUserDelete(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        UserRepository                 $userRepository,
        UserDeleteRepository           $userDeleteRepository,
        MailerInterface                $mailer,
        TranslateService               $translateService
    ): Response
    {
        $adminUserDeleteQuery = $requestService->getRequestBodyContent($request, AdminUserDeleteQuery::class);

        if ($adminUserDeleteQuery instanceof AdminUserDeleteQuery) {

            $user = $userRepository->findOneBy([
                "id" => $adminUserDeleteQuery->getUserId()
            ]);

            if ($user === null) {
                $endpointLogger->error("User dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserDontExists")]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error("User is admin");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserDontExists")]);
            }

            $userDelete = $userDeleteRepository->findOneBy([
                "user" => $user->getId()
            ]);

            if ($userDelete === null) {
                $userDelete = new UserDelete($user);
            }

            $userDelete->setDeleted(true);
            $userDelete->setDateDeleted(new \DateTime("Now"));

            $userDeleteRepository->add($userDelete);

            if ($_ENV["APP_ENV"] !== "test") {
                $email = (new TemplatedEmail())
                    ->from($_ENV["INSTITUTION_EMAIL"])
                    ->to($user->getUserInformation()->getEmail())
                    ->subject($translateService->getTranslation("AccountDeletedSubject"))
                    ->htmlTemplate('emails/userDeleted.html.twig')
                    ->context([
                        "userName" => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                        "lang" => $request->getPreferredLanguage() != null ? $request->getPreferredLanguage() : $translateService->getLocate()
                    ]);
                $mailer->send($email);
            }

            return ResponseTool::getResponse();
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @param UserDeleteRepository $userDeleteRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/user/delete/list", name: "adminUserDeleteList", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is returning list of users to delete",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminUserDeleteListQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AdminUserDeleteListSuccessModel::class)
            )
        ]
    )]
    public function adminUserDeleteList(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        UserRepository                 $userRepository,
        UserDeleteRepository           $userDeleteRepository,
        TranslateService               $translateService
    ): Response
    {
        $adminUserDeleteListQuery = $requestService->getRequestBodyContent($request, AdminUserDeleteListQuery::class);

        if ($adminUserDeleteListQuery instanceof AdminUserDeleteListQuery) {

            $successModel = new AdminUserDeleteListSuccessModel();

            $minResult = $adminUserDeleteListQuery->getPage() * $adminUserDeleteListQuery->getLimit();
            $maxResult = $adminUserDeleteListQuery->getLimit() + $minResult;

            $allDeleteUsers = $userDeleteRepository->findBy([
                "deleted" => true
            ]);

            foreach ($allDeleteUsers as $index => $userDelete) {

                $user = $userDelete->getUser();

                if ($index < $minResult || $userRepository->userIsAdmin($user)) {
                    continue;
                }

                if ($index < $maxResult) {
                    $userDeleteModel = new AdminUserDeleteModel(
                        $user->getId(),
                        $user->isActive(),
                        $user->isBanned(),
                        $user->getUserInformation()->getEmail(),
                        $user->getUserInformation()->getFirstname(),
                        $userDelete->getDeleted(),
                        $userDelete->getDeclined()
                    );


                    if ($userDelete->getDateDeleted() != null) {
                        $userDeleteModel->setDateDeleted($userDelete->getDateDeleted());
                    }

                    $successModel->addUser($userDeleteModel);
                } else {
                    break;
                }
            }

            $successModel->setPage($adminUserDeleteListQuery->getPage());
            $successModel->setLimit($adminUserDeleteListQuery->getLimit());

            $successModel->setMaxPage(ceil(count($allDeleteUsers) / $adminUserDeleteListQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @param UserDeleteRepository $userDeleteRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/user/to/delete/list", name: "adminUserToDeleteList", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is returning list of already delete users",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminUserDeleteListQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AdminUserDeleteListSuccessModel::class)
            )
        ]
    )]
    public function adminUserToDeleteList(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        UserRepository                 $userRepository,
        UserDeleteRepository           $userDeleteRepository,
        TranslateService               $translateService
    ): Response
    {
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
                        $user->getId(),
                        $user->isActive(),
                        $user->isBanned(),
                        $user->getUserInformation()->getEmail(),
                        $user->getUserInformation()->getFirstname(),
                        $userDelete->getDeleted(),
                        $userDelete->getDeclined()
                    );


                    if ($userDelete->getDateDeleted() != null) {
                        $userDeleteModel->setDateDeleted($userDelete->getDateDeleted());
                    }

                    $successModel->addUser($userDeleteModel);
                } else {
                    break;
                }
            }

            $successModel->setPage($adminUserDeleteListQuery->getPage());
            $successModel->setLimit($adminUserDeleteListQuery->getLimit());

            $successModel->setMaxPage(ceil(count($allDeleteUsers) / $adminUserDeleteListQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserDeleteRepository $userDeleteRepository
     * @param MailerInterface $mailer
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     * @throws TransportExceptionInterface
     */
    #[Route("/api/admin/user/delete/accept", name: "adminUserDeleteAccept", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Patch(
        description: "Endpoint is deleting given user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminUserDeleteAcceptQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function adminUserDeleteAccept(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        UserDeleteRepository           $userDeleteRepository,
        MailerInterface                $mailer,
        TranslateService               $translateService
    ): Response
    {
        $adminUserDeleteAcceptQuery = $requestService->getRequestBodyContent($request, AdminUserDeleteAcceptQuery::class);

        if ($adminUserDeleteAcceptQuery instanceof AdminUserDeleteAcceptQuery) {

            $userDelete = $userDeleteRepository->findOneBy([
                "user" => $adminUserDeleteAcceptQuery->getUserId()
            ]);

            if ($userDelete === null) {
                $endpointLogger->error("User dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserDeleteDontExists")]);
            }
            $user = $userDelete->getUser();

            $userInDelete = $userDeleteRepository->userInList($user);

            if ($userInDelete) {
                $endpointLogger->error("User in list");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserDeleted")]);
            }

            $userDelete->setDeleted(true);
            $userDelete->setDateDeleted(new \DateTime("Now"));

            $userDeleteRepository->add($userDelete);

            if ($_ENV["APP_ENV"] !== "test") {
                $email = (new TemplatedEmail())
                    ->from($_ENV["INSTITUTION_EMAIL"])
                    ->to($user->getUserInformation()->getEmail())
                    ->subject($translateService->getTranslation("AccountDeletedSubject"))
                    ->htmlTemplate('emails/userDeleted.html.twig')
                    ->context([
                        "userName" => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                        "lang" => $request->getPreferredLanguage() != null ? $request->getPreferredLanguage() : $translateService->getLocate()
                    ]);
                $mailer->send($email);
            }

            return ResponseTool::getResponse();
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @param UserDeleteRepository $userDeleteRepository
     * @param MailerInterface $mailer
     * @param NotificationRepository $notificationRepository
     * @param TranslateService $translateService
     * @param TagAwareCacheInterface $stockCache
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidArgumentException
     * @throws InvalidJsonDataException
     * @throws NotificationException
     * @throws TransportExceptionInterface
     */
    #[Route("/api/admin/user/delete/decline", name: "adminUserDeleteDecline", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Patch(
        description: "Endpoint is declining user request to delete his account",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminUserDeleteDeclineQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function adminUserDeleteDecline(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        UserRepository                 $userRepository,
        UserDeleteRepository           $userDeleteRepository,
        MailerInterface                $mailer,
        NotificationRepository         $notificationRepository,
        TranslateService               $translateService,
        TagAwareCacheInterface         $stockCache
    ): Response
    {
        $adminUserDeleteDeclineQuery = $requestService->getRequestBodyContent($request, AdminUserDeleteDeclineQuery::class);

        if ($adminUserDeleteDeclineQuery instanceof AdminUserDeleteDeclineQuery) {

            $userDelete = $userDeleteRepository->findOneBy([
                "user" => $adminUserDeleteDeclineQuery->getUserId()
            ]);

            if ($userDelete === null) {
                $endpointLogger->error("User dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserDeleteDontExists")]);
            }

            $user = $userDelete->getUser();

            $userInDelete = $userDeleteRepository->userInList($user);

            if ($userInDelete) {
                $endpointLogger->error("User in list");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserDeleted")]);
            }

            $userDelete->setDeclined(true);

            $userDeleteRepository->add($userDelete, false);

            $user->setActive(true);

            $userRepository->add($user);

            if ($_ENV["APP_ENV"] !== "test") {
                $email = (new TemplatedEmail())
                    ->from($_ENV["INSTITUTION_EMAIL"])
                    ->to($user->getUserInformation()->getEmail())
                    ->subject($translateService->getTranslation("DeletionRejectedSubject"))
                    ->htmlTemplate('emails/userDeletedDecline.html.twig')
                    ->context([
                        "userName" => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                        "lang" => $request->getPreferredLanguage() != null ? $request->getPreferredLanguage() : $translateService->getLocate()
                    ]);
                $mailer->send($email);
            }

            $notificationBuilder = new NotificationBuilder();

            $notification = $notificationBuilder
                ->setType(NotificationType::USER_DELETE_DECLINE)
                ->setAction($userDelete->getId())
                ->addUser($user)
                ->setUserAction(NotificationUserType::SYSTEM)
                ->build($stockCache);

            $notificationRepository->add($notification);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param NotificationRepository $notificationRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/user/notifications", name: "adminUserNotifications", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is returning list of notifications in system",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminUserNotificationsQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AdminUserNotificationsSuccessModel::class)
            )
        ]
    )]
    public function adminUserNotifications(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        NotificationRepository         $notificationRepository,
        TranslateService               $translateService
    ): Response
    {
        $adminUserNotificationsQuery = $requestService->getRequestBodyContent($request, AdminUserNotificationsQuery::class);

        if ($adminUserNotificationsQuery instanceof AdminUserNotificationsQuery) {

            $notificationSearchData = $adminUserNotificationsQuery->getSearchData();

            $text = null;
            $type = null;
            $deleted = null;
            $order = null;

            if (array_key_exists('text', $notificationSearchData)) {
                $text = ($notificationSearchData['text'] && '' != $notificationSearchData['text']) ? "%" . $notificationSearchData['text'] . "%" : null;
            }
            if (array_key_exists('type', $notificationSearchData)) {
                $type = $notificationSearchData['type'];
            }
            if (array_key_exists('deleted', $notificationSearchData)) {
                $deleted = $notificationSearchData['deleted'];
            }
            if (array_key_exists('order', $notificationSearchData)) {
                $order = $notificationSearchData['order'];
            }

            $allUserSystemNotifications = $notificationRepository->getSearchNotifications($text, $type, $deleted, $order);

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
                ceil(count($allUserSystemNotifications) / $adminUserNotificationsQuery->getLimit())
            );

            return ResponseTool::getResponse($systemNotificationSuccessModel);
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param NotificationRepository $notificationRepository
     * @param UserRepository $userRepository
     * @param RoleRepository $roleRepository
     * @param AudiobookRepository $audiobookRepository
     * @param InstitutionRepository $institutionRepository
     * @param AudiobookCategoryRepository $categoryRepository
     * @param TranslateService $translateService
     * @param TagAwareCacheInterface $stockCache
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidArgumentException
     * @throws InvalidJsonDataException
     * @throws NotificationException
     */
    #[Route("/api/admin/user/notification", name: "adminUserNotificationPut", methods: ["PUT"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Put(
        description: "Endpoint is adding notification",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminUserNotificationPutQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Success",
            )
        ]
    )]
    public function adminUserNotificationPut(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        NotificationRepository         $notificationRepository,
        UserRepository                 $userRepository,
        RoleRepository                 $roleRepository,
        AudiobookRepository            $audiobookRepository,
        InstitutionRepository          $institutionRepository,
        AudiobookCategoryRepository    $categoryRepository,
        TranslateService               $translateService,
        TagAwareCacheInterface         $stockCache
    ): Response
    {
        $adminUserNotificationPutQuery = $requestService->getRequestBodyContent($request, AdminUserNotificationPutQuery::class);

        if ($adminUserNotificationPutQuery instanceof AdminUserNotificationPutQuery) {

            $additionalData = $adminUserNotificationPutQuery->getAdditionalData();

            switch ($adminUserNotificationPutQuery->getNotificationType()) {
                case NotificationType::NORMAL:

                    $userRole = $roleRepository->findOneBy([
                        "name" => UserRolesNames::USER->value
                    ]);

                    $users = $userRepository->getUsersByRole($userRole);
                    $notificationBuilder = new NotificationBuilder();

                    $institution = $institutionRepository->findOneBy([
                        "name" => $_ENV["INSTITUTION_NAME"]
                    ]);

                    $notificationBuilder
                        ->setType($adminUserNotificationPutQuery->getNotificationType())
                        ->setUserAction($adminUserNotificationPutQuery->getNotificationUserType())
                        ->setAction($institution->getId());

                    if (array_key_exists("text", $additionalData)) {
                        $notificationBuilder->setText($additionalData["text"]);
                    }

                    foreach ($users as $user) {
                        $notificationBuilder->addUser($user);
                    }

                    $notification = $notificationBuilder->build($stockCache);

                    $notificationRepository->add($notification);
                    break;
                case NotificationType::ADMIN:
                    if (!array_key_exists("userId", $additionalData)) {
                        $endpointLogger->error("Invalid given Query no userId");
                        $translateService->setPreferredLanguage($request);
                        throw new InvalidJsonDataException($translateService);
                    }

                    $user = $userRepository->findOneBy([
                        "id" => $additionalData["userId"]
                    ]);

                    if ($user === null) {
                        $endpointLogger->error("User dont exist");
                        $translateService->setPreferredLanguage($request);
                        throw new DataNotFoundException([$translateService->getTranslation("UserDontExists")]);
                    }

                    $notificationBuilder = new NotificationBuilder();

                    $notificationBuilder
                        ->setType($adminUserNotificationPutQuery->getNotificationType())
                        ->setUserAction($adminUserNotificationPutQuery->getNotificationUserType())
                        ->addUser($user)
                        ->setAction($user->getId());

                    if (array_key_exists("text", $additionalData)) {
                        $notificationBuilder->setText($additionalData["text"]);
                    }

                    $notification = $notificationBuilder->build($stockCache);

                    $notificationRepository->add($notification);

                    break;
                case NotificationType::NEW_CATEGORY:
                    if (!array_key_exists("categoryKey", $additionalData)) {
                        $endpointLogger->error("Invalid given Query no categoryKey");
                        $translateService->setPreferredLanguage($request);
                        throw new InvalidJsonDataException($translateService);
                    }

                    $userRole = $roleRepository->findOneBy([
                        "name" => UserRolesNames::USER
                    ]);

                    $users = $userRepository->getUsersByRole($userRole);
                    $notificationBuilder = new NotificationBuilder();

                    $category = $categoryRepository->findOneBy([
                        "categoryKey" => $additionalData["categoryKey"]
                    ]);

                    if ($category === null) {
                        $endpointLogger->error("Category dont exist");
                        $translateService->setPreferredLanguage($request);
                        throw new DataNotFoundException([$translateService->getTranslation("CategoryDontExists")]);
                    }

                    $notificationBuilder
                        ->setType($adminUserNotificationPutQuery->getNotificationType())
                        ->setUserAction($adminUserNotificationPutQuery->getNotificationUserType())
                        ->setAction($category->getId())
                        ->setCategoryKey($category->getCategoryKey());

                    if (array_key_exists("text", $additionalData)) {
                        $notificationBuilder->setText($additionalData["text"]);
                    }

                    foreach ($users as $user) {
                        $notificationBuilder->addUser($user);
                    }

                    $notification = $notificationBuilder->build($stockCache);

                    $notificationRepository->add($notification);
                    break;
                case NotificationType::NEW_AUDIOBOOK:
                    if (!array_key_exists("actionId", $additionalData)) {
                        $endpointLogger->error("Invalid given Query no actionId");
                        $translateService->setPreferredLanguage($request);
                        throw new InvalidJsonDataException($translateService);
                    }

                    $audiobook = $audiobookRepository->findOneBy([
                        "id" => $additionalData["actionId"]
                    ]);

                    if ($audiobook === null) {
                        $endpointLogger->error("Audiobook dont exist");
                        $translateService->setPreferredLanguage($request);
                        throw new DataNotFoundException([$translateService->getTranslation("AudiobookDontExists")]);
                    }

                    $users = $userRepository->getUsersWhereAudiobookInProposed($audiobook);
                    $notificationBuilder = new NotificationBuilder();

                    $notificationBuilder
                        ->setType($adminUserNotificationPutQuery->getNotificationType())
                        ->setUserAction($adminUserNotificationPutQuery->getNotificationUserType())
                        ->setAction($additionalData["actionId"]);

                    if (array_key_exists("text", $additionalData)) {
                        $notificationBuilder->setText($additionalData["text"]);
                    }

                    foreach ($users as $user) {
                        $notificationBuilder->addUser($user);
                    }

                    $notification = $notificationBuilder->build($stockCache);

                    $notificationRepository->add($notification);
                    break;
            }

            return ResponseTool::getResponse(httpCode: 201);
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);

    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param UserRepository $userRepository
     * @param LoggerInterface $endpointLogger
     * @param NotificationRepository $notificationRepository
     * @param RoleRepository $roleRepository
     * @param TranslateService $translateService
     * @param TagAwareCacheInterface $stockCache
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidArgumentException
     * @throws InvalidJsonDataException
     * @throws NotificationException
     */
    #[Route("/api/admin/user/notification", name: "adminUserNotificationPatch", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Patch(
        description: "Endpoint is editing notification",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminUserNotificationPatchQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function adminUserNotificationPatch(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        UserRepository                 $userRepository,
        LoggerInterface                $endpointLogger,
        NotificationRepository         $notificationRepository,
        RoleRepository                 $roleRepository,
        TranslateService               $translateService,
        TagAwareCacheInterface         $stockCache
    ): Response
    {
        $adminUserNotificationPatchQuery = $requestService->getRequestBodyContent($request, AdminUserNotificationPatchQuery::class);

        if ($adminUserNotificationPatchQuery instanceof AdminUserNotificationPatchQuery) {

            $notification = $notificationRepository->findOneBy([
                "id" => $adminUserNotificationPatchQuery->getNotificationId()
            ]);

            if ($notification === null) {
                $endpointLogger->error("Notification dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("NotificationDontExists")]);
            }

            $notificationBuilder = new NotificationBuilder($notification);

            $notificationBuilder
                ->setType($adminUserNotificationPatchQuery->getNotificationType())
                ->setUserAction($adminUserNotificationPatchQuery->getNotificationUserType());

            $userRole = $roleRepository->findOneBy([
                "name" => UserRolesNames::USER
            ]);

            $users = $userRepository->getUsersByRole($userRole);

            foreach ($users as $user) {
                $notificationBuilder->addUser($user);
            }

            $additionalData = $adminUserNotificationPatchQuery->getAdditionalData();

            if (array_key_exists("text", $additionalData)) {
                $notificationBuilder->setText($additionalData["text"]);
            }
            if (array_key_exists("categoryKey", $additionalData)) {
                $notificationBuilder->setCategoryKey($additionalData["categoryKey"]);
            } else {
                $notificationBuilder->setAction($adminUserNotificationPatchQuery->getActionId());
            }

            $notification = $notificationBuilder->build($stockCache);

            $notificationRepository->add($notification);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param NotificationRepository $notificationRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/user/notification/delete", name: "adminUserNotificationDelete", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Patch(
        description: "Endpoint is deleting notification",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminUserNotificationDeleteQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function adminUserNotificationDelete(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        NotificationRepository         $notificationRepository,
        TranslateService               $translateService
    ): Response
    {
        $adminUserNotificationDeleteQuery = $requestService->getRequestBodyContent($request, AdminUserNotificationDeleteQuery::class);

        if ($adminUserNotificationDeleteQuery instanceof AdminUserNotificationDeleteQuery) {

            $notification = $notificationRepository->findOneBy([
                "id" => $adminUserNotificationDeleteQuery->getNotificationId()
            ]);

            if ($notification === null) {
                $endpointLogger->error("Notification dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("NotificationDontExists")]);
            }

            $notification->setDeleted($adminUserNotificationDeleteQuery->isDelete());

            $notificationRepository->add($notification);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }
}