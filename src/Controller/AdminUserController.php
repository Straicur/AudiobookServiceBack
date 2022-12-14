<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Builder\NotificationBuilder;
use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Enums\UserRoles;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Exception\NotificationException;
use App\Model\AdminUserDeleteListSuccessModel;
use App\Model\AdminUserDetailsSuccessModel;
use App\Model\AdminUserNotificationsSuccessModel;
use App\Model\AdminUsersSuccessModel;
use App\Model\DataNotFoundModel;
use App\Model\JsonDataInvalidModel;
use App\Model\NotAuthorizeModel;
use App\Model\PermissionNotGrantedModel;
use App\Model\UserDeleteModel;
use App\Model\UserModel;
use App\Query\AdminUserActivateQuery;
use App\Query\AdminUserBanQuery;
use App\Query\AdminUserChangePasswordQuery;
use App\Query\AdminUserChangePhoneQuery;
use App\Query\AdminUserDeleteAcceptQuery;
use App\Query\AdminUserDeleteDeclineQuery;
use App\Query\AdminUserDeleteListQuery;
use App\Query\AdminUserDeleteQuery;
use App\Query\AdminUserDetailsQuery;
use App\Query\AdminUserNotificationPatchQuery;
use App\Query\AdminUserNotificationPutQuery;
use App\Query\AdminUserNotificationsQuery;
use App\Query\AdminUserRoleAddQuery;
use App\Query\AdminUserRoleRemoveQuery;
use App\Query\AdminUsersQuery;
use App\Repository\AudiobookRepository;
use App\Repository\NotificationRepository;
use App\Repository\RoleRepository;
use App\Repository\UserDeleteRepository;
use App\Repository\UserInformationRepository;
use App\Repository\UserPasswordRepository;
use App\Repository\UserRepository;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Tool\ResponseTool;
use App\ValueGenerator\PasswordHashGenerator;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * AdminUserController
 */
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
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @param RoleRepository $roleRepository
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
        RoleRepository                 $roleRepository
    ): Response
    {
        $adminUserRoleAddQuery = $requestService->getRequestBodyContent($request, AdminUserRoleAddQuery::class);

        if ($adminUserRoleAddQuery instanceof AdminUserRoleAddQuery) {

            $user = $userRepository->findOneBy([
                "id" => $adminUserRoleAddQuery->getUserId()
            ]);

            if ($user == null) {
                $endpointLogger->error("User dont exist");
                throw new DataNotFoundException(["adminUser.role.add.user.not.exist"]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error("User is admin");
                throw new DataNotFoundException(["adminUser.role.add.user.invalid.permission"]);
            }

            switch ($adminUserRoleAddQuery->getRole()) {
                case UserRoles::GUEST:
                    $guestRole = $roleRepository->findOneBy([
                        "name" => "Guest"
                    ]);
                    $user->addRole($guestRole);
                    break;

                case UserRoles::USER:
                    $userRole = $roleRepository->findOneBy([
                        "name" => "User"
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
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminUser.role.add.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @param RoleRepository $roleRepository
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
        RoleRepository                 $roleRepository
    ): Response
    {
        $adminUserRoleRemoveQuery = $requestService->getRequestBodyContent($request, AdminUserRoleRemoveQuery::class);

        if ($adminUserRoleRemoveQuery instanceof AdminUserRoleRemoveQuery) {

            $user = $userRepository->findOneBy([
                "id" => $adminUserRoleRemoveQuery->getUserId()
            ]);

            if ($user == null) {
                $endpointLogger->error("User dont exist");
                throw new DataNotFoundException(["adminUser.role.remove.user.not.exist"]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error("User is admin");
                throw new DataNotFoundException(["adminUser.role.remove.user.invalid.permission"]);
            }

            switch ($adminUserRoleRemoveQuery->getRole()) {
                case UserRoles::GUEST:
                    $guestRole = $roleRepository->findOneBy([
                        "name" => "Guest"
                    ]);
                    $user->removeRole($guestRole);
                    break;

                case UserRoles::USER:
                    $userRole = $roleRepository->findOneBy([
                        "name" => "User"
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
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminUser.role.remove.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
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
        UserRepository                 $userRepository
    ): Response
    {
        $adminUserActivateQuery = $requestService->getRequestBodyContent($request, AdminUserActivateQuery::class);

        if ($adminUserActivateQuery instanceof AdminUserActivateQuery) {

            $user = $userRepository->findOneBy([
                "id" => $adminUserActivateQuery->getUserId()
            ]);

            if ($user == null) {
                $endpointLogger->error("User dont exist");
                throw new DataNotFoundException(["adminUser.activate.user.not.exist"]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error("User is admin");
                throw new DataNotFoundException(["adminUser.activate.user.invalid.permission"]);
            }

            $user->setActive(true);

            $userRepository->add($user);

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminUser.activate.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
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
        UserRepository                 $userRepository
    ): Response
    {
        $adminUserBanQuery = $requestService->getRequestBodyContent($request, AdminUserBanQuery::class);

        if ($adminUserBanQuery instanceof AdminUserBanQuery) {

            $user = $userRepository->findOneBy([
                "id" => $adminUserBanQuery->getUserId()
            ]);

            if ($user == null) {
                $endpointLogger->error("User dont exist");
                throw new DataNotFoundException(["adminUser.ban.user.not.exist"]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error("User is admin");
                throw new DataNotFoundException(["adminUser.ban.user.invalid.permission"]);
            }

            $user->setBanned($adminUserBanQuery->isBanned());

            $userRepository->add($user);

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminUser.ban.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @param UserPasswordRepository $userPasswordRepository
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
        UserPasswordRepository         $userPasswordRepository
    ): Response
    {
        $adminUserChangePasswordQuery = $requestService->getRequestBodyContent($request, AdminUserChangePasswordQuery::class);

        if ($adminUserChangePasswordQuery instanceof AdminUserChangePasswordQuery) {

            $user = $userRepository->findOneBy([
                "id" => $adminUserChangePasswordQuery->getUserId()
            ]);

            if ($user == null) {
                $endpointLogger->error("User dont exist");
                throw new DataNotFoundException(["adminUser.change.password.not.exist"]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error("User is admin");
                throw new DataNotFoundException(["adminUser.change.password.user.invalid.permission"]);
            }
            $userPassword = $userPasswordRepository->findOneBy([
                "user" => $user->getId()
            ]);

            $passwordGenerator = new PasswordHashGenerator($adminUserChangePasswordQuery->getNewPassword());

            $userPassword->setPassword($passwordGenerator);

            $userPasswordRepository->add($userPassword);

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminUser.change.password.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @param UserInformationRepository $userInformationRepository
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
        UserInformationRepository      $userInformationRepository
    ): Response
    {
        $adminUserChangePhoneQuery = $requestService->getRequestBodyContent($request, AdminUserChangePhoneQuery::class);

        if ($adminUserChangePhoneQuery instanceof AdminUserChangePhoneQuery) {

            $user = $userRepository->findOneBy([
                "id" => $adminUserChangePhoneQuery->getUserId()
            ]);

            if ($user == null) {
                $endpointLogger->error("User dont exist");
                throw new DataNotFoundException(["adminUser.change.phone.not.exist"]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error("User is admin");
                throw new DataNotFoundException(["adminUser.change.phone.user.invalid.permission"]);
            }
            $userInfo = $user->getUserInformation();

            $userInfo->setPhoneNumber($adminUserChangePhoneQuery->getNewPhone());

            $userInformationRepository->add($userInfo);

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminUser.change.phone.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
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
        UserRepository                 $userRepository
    ): Response
    {
        $adminUsersQuery = $requestService->getRequestBodyContent($request, AdminUsersQuery::class);

        if ($adminUsersQuery instanceof AdminUsersQuery) {

            $successModel = new AdminUsersSuccessModel();

            $minResult = $adminUsersQuery->getPage() * $adminUsersQuery->getLimit();
            $maxResult = $adminUsersQuery->getLimit() + $minResult;

            $allUsers = $userRepository->findAll();

            foreach ($allUsers as $index => $user) {
                if ($index < $minResult || $userRepository->userIsAdmin($user)) {
                    continue;
                } elseif ($index < $maxResult) {

                    $successModel->addUser(new UserModel(
                        $user->getId(),
                        $user->isActive(),
                        $user->isBanned(),
                        $user->getUserInformation()->getEmail(),
                        $user->getUserInformation()->getFirstname()
                    ));
                } else {
                    break;
                }
            }

            $successModel->setPage($adminUsersQuery->getPage());
            $successModel->setLimit($adminUsersQuery->getLimit());

            $successModel->setMaxPage(floor(count($allUsers) / $adminUsersQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminUsers.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/user/details", name: "adminUserDetails", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is returning details of given user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminUserDetailsQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AdminUserDetailsSuccessModel::class)
            )
        ]
    )]
    public function adminUserDetails(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        UserRepository                 $userRepository
    ): Response
    {
        $adminUserDetailsQuery = $requestService->getRequestBodyContent($request, AdminUserDetailsQuery::class);

        if ($adminUserDetailsQuery instanceof AdminUserDetailsQuery) {

            $user = $userRepository->findOneBy([
                "id" => $adminUserDetailsQuery->getUserId()
            ]);

            if ($user == null) {
                $endpointLogger->error("User dont exist");
                throw new DataNotFoundException(["adminUser.details.user.not.exist"]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error("User is admin");
                throw new DataNotFoundException(["adminUser.details.user.invalid.permission"]);
            }
            $successModel = new AdminUserDetailsSuccessModel(
                $user->getId(),
                $user->getDateCreate(),
                $user->isActive(),
                $user->isBanned(),
                $user->getUserInformation()->getEmail(),
                $user->getUserInformation()->getPhoneNumber(),
                $user->getUserInformation()->getFirstname(),
                $user->getUserInformation()->getLastname()
            );

            foreach ($user->getRoles() as $role) {
                switch ($role->getName()) {
                    case "Guest":
                        $successModel->addRole(UserRoles::GUEST);
                        break;
                    case "User":
                        $successModel->addRole(UserRoles::USER);
                        break;
                    case "Administrator":
                        $successModel->addRole(UserRoles::ADMINISTRATOR);
                        break;
                }
            }

            return ResponseTool::getResponse($successModel);
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminUser.details.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @param MailerInterface $mailer
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
        MailerInterface                $mailer
    ): Response
    {
        $adminUserDeleteQuery = $requestService->getRequestBodyContent($request, AdminUserDeleteQuery::class);

        if ($adminUserDeleteQuery instanceof AdminUserDeleteQuery) {

            $user = $userRepository->findOneBy([
                "id" => $adminUserDeleteQuery->getUserId()
            ]);

            if ($user == null) {
                $endpointLogger->error("User dont exist");
                throw new DataNotFoundException(["adminUser.delete.user.not.exist"]);
            }

            if ($userRepository->userIsAdmin($user)) {
                $endpointLogger->error("User is admin");
                throw new DataNotFoundException(["adminUser.delete.user.invalid.permission"]);
            }

            if ($_ENV["APP_ENV"] != "test") {
                $email = (new TemplatedEmail())
                    ->from($_ENV["INSTITUTION_EMAIL"])
                    ->to($user->getUserInformation()->getEmail())
                    ->subject('Konto usuni??te')
                    ->htmlTemplate('emails/userDeleted.html.twig')
                    ->context([
                        "userName" => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname()
                    ]);
                $mailer->send($email);
            }

            $userRepository->remove($user);

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminUser.delete.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @param UserDeleteRepository $userDeleteRepository
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
        UserDeleteRepository           $userDeleteRepository
    ): Response
    {
        $adminUserDeleteListQuery = $requestService->getRequestBodyContent($request, AdminUserDeleteListQuery::class);

        if ($adminUserDeleteListQuery instanceof AdminUserDeleteListQuery) {

            $successModel = new AdminUserDeleteListSuccessModel();

            $minResult = $adminUserDeleteListQuery->getPage() * $adminUserDeleteListQuery->getLimit();
            $maxResult = $adminUserDeleteListQuery->getLimit() + $minResult;

            $allDeleteUsers = $userDeleteRepository->findAll();

            foreach ($allDeleteUsers as $index => $userDelete) {

                $user = $userDelete->getUser();

                if ($index < $minResult || $userRepository->userIsAdmin($user)) {
                    continue;
                } elseif ($index < $maxResult) {
                    $userDeleteModel = new UserDeleteModel(
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

            $successModel->setMaxPage(floor(count($allDeleteUsers) / $adminUserDeleteListQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminUser.delete.list.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @param UserDeleteRepository $userDeleteRepository
     * @return Response
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/user/to/delete/list", name: "adminUserToDeleteList", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is returning list of users reade to delete",
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
        UserDeleteRepository           $userDeleteRepository
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
                } elseif ($index < $maxResult) {
                    $userDeleteModel = new UserDeleteModel(
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

            $successModel->setMaxPage(floor(count($allDeleteUsers) / $adminUserDeleteListQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminUser.to.delete.list.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserDeleteRepository $userDeleteRepository
     * @param MailerInterface $mailer
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
        MailerInterface                $mailer
    ): Response
    {
        $adminUserDeleteAcceptQuery = $requestService->getRequestBodyContent($request, AdminUserDeleteAcceptQuery::class);

        if ($adminUserDeleteAcceptQuery instanceof AdminUserDeleteAcceptQuery) {

            $userDelete = $userDeleteRepository->findOneBy([
                "user" => $adminUserDeleteAcceptQuery->getUserId()
            ]);

            if ($userDelete == null) {
                $endpointLogger->error("User dont exist");
                throw new DataNotFoundException(["adminUser.delete.accept.user.not.exist"]);
            }
            $user = $userDelete->getUser();

            $userInDelete = $userDeleteRepository->userInList($user);

            if ($userInDelete) {
                $endpointLogger->error("User in list");
                throw new DataNotFoundException(["adminUser.delete.accept.exist"]);
            }

            $userDelete->setDeleted(true);
            $userDelete->setDateDeleted(new \DateTime("Now"));

            if ($_ENV["APP_ENV"] != "test") {
                $email = (new TemplatedEmail())
                    ->from($_ENV["INSTITUTION_EMAIL"])
                    ->to($user->getUserInformation()->getEmail())
                    ->subject('Konto usuni??te')
                    ->htmlTemplate('emails/userDeleted.html.twig')
                    ->context([
                        "userName" => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname()
                    ]);
                $mailer->send($email);
            }

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminUser.delete.accept.invalid.query");
        }
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
     * @return Response
     * @throws DataNotFoundException
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
        NotificationRepository         $notificationRepository
    ): Response
    {
        $adminUserDeleteDeclineQuery = $requestService->getRequestBodyContent($request, AdminUserDeleteDeclineQuery::class);

        if ($adminUserDeleteDeclineQuery instanceof AdminUserDeleteDeclineQuery) {

            $userDelete = $userDeleteRepository->findOneBy([
                "user" => $adminUserDeleteDeclineQuery->getUserId()
            ]);

            if ($userDelete == null) {
                $endpointLogger->error("User dont exist");
                throw new DataNotFoundException(["adminUser.delete.decline.user.not.exist"]);
            }

            $user = $userDelete->getUser();

            $userInDelete = $userDeleteRepository->userInList($user);

            if ($userInDelete) {
                $endpointLogger->error("User in list");
                throw new DataNotFoundException(["adminUser.delete.decline.user.not.in.list.exist"]);
            }

            $userDelete->setDeclined(true);

            $userDeleteRepository->add($userDelete, false);

            $user->setActive(true);

            $userRepository->add($user);

            if ($_ENV["APP_ENV"] != "test") {
                $email = (new TemplatedEmail())
                    ->from($_ENV["INSTITUTION_EMAIL"])
                    ->to($user->getUserInformation()->getEmail())
                    ->subject('Usuni??cie odrzucone')
                    ->htmlTemplate('emails/userDeletedDecline.html.twig')
                    ->context([
                        "userName" => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname()
                    ]);
                $mailer->send($email);
            }

            $notificationBuilder = new NotificationBuilder();

            $notification = $notificationBuilder
                ->setType(NotificationType::PROPOSED)
                ->setAction($userDelete->getId())
                ->setUser($user)
                ->setUserAction(NotificationUserType::SYSTEM)
                ->build();

            $notificationRepository->add($notification);

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminUser.delete.decline.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @param NotificationRepository $notificationRepository
     * @return Response
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/user/notifications", name: "adminUserNotifications", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is returning list of negotiations in system",
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
        UserRepository                 $userRepository,
        NotificationRepository         $notificationRepository
    ): Response
    {
        $adminUserNotificationsQuery = $requestService->getRequestBodyContent($request, AdminUserNotificationsQuery::class);

        if ($adminUserNotificationsQuery instanceof AdminUserNotificationsQuery) {

            $allUserSystemNotifications = $notificationRepository->findBy([], [
                "dateAdd" => "DESC"
            ],
                $adminUserNotificationsQuery->getLimit(), $adminUserNotificationsQuery->getPage());

            $systemNotifications = [];

            foreach ($allUserSystemNotifications as $notification) {
                $systemNotifications[] = NotificationBuilder::read($notification);
            }

            $systemNotificationSuccessModel = new AdminUserNotificationsSuccessModel(
                $systemNotifications,
                $adminUserNotificationsQuery->getPage(),
                $adminUserNotificationsQuery->getLimit(),
                floor(floor(count($allUserSystemNotifications) / $adminUserNotificationsQuery->getLimit()))
            );

            return ResponseTool::getResponse($systemNotificationSuccessModel);
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminUser.notifications.invalid.query");
        }
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
     * @return Response
     * @throws DataNotFoundException
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
        AudiobookRepository            $audiobookRepository
    ): Response
    {
        $adminUserNotificationPutQuery = $requestService->getRequestBodyContent($request, AdminUserNotificationPutQuery::class);

        if ($adminUserNotificationPutQuery instanceof AdminUserNotificationPutQuery) {

            $additionalData = $adminUserNotificationPutQuery->getAdditionalData();

            switch ($adminUserNotificationPutQuery->getNotificationType()) {
                case NotificationType::NORMAL:

                    $userRole = $roleRepository->findOneBy([
                        "name" => "User"
                    ]);

                    $users = $userRepository->getUsersByRole($userRole);

                    foreach ($users as $user) {
                        $notificationBuilder = new NotificationBuilder();

                        $notificationBuilder
                            ->setType($adminUserNotificationPutQuery->getNotificationType())
                            ->setUserAction($adminUserNotificationPutQuery->getNotificationUserType())
                            ->setUser($user)
                            ->setAction($user->getId());

                        if (array_key_exists("text", $additionalData)) {
                            $notificationBuilder->setText($additionalData["text"]);
                        }

                        $notification = $notificationBuilder->build();

                        $notificationRepository->add($notification);
                    }

                    break;
                case NotificationType::ADMIN:
                    if (!array_key_exists("userId", $additionalData)) {
                        $endpointLogger->error("Invalid given Query no userId");
                        throw new InvalidJsonDataException("adminUser.notification.put.invalid.query");
                    }

                    $user = $userRepository->findOneBy([
                        "id" => $additionalData["userId"]
                    ]);

                    if ($user == null) {
                        $endpointLogger->error("User dont exist");
                        throw new DataNotFoundException(["adminUser.notification.put.user.dont.exist"]);
                    }

                    $notificationBuilder = new NotificationBuilder();

                    $notificationBuilder
                        ->setType($adminUserNotificationPutQuery->getNotificationType())
                        ->setUserAction($adminUserNotificationPutQuery->getNotificationUserType())
                        ->setUser($user)
                        ->setAction($user->getId());

                    if (array_key_exists("text", $additionalData)) {
                        $notificationBuilder->setText($additionalData["text"]);
                    }

                    $notification = $notificationBuilder->build();

                    $notificationRepository->add($notification);

                    break;
                case NotificationType::PROPOSED:
                    break;
                case NotificationType::NEW_CATEGORY:
                    if (!array_key_exists("actionId", $additionalData)) {
                        $endpointLogger->error("Invalid given Query no actionId");
                        throw new InvalidJsonDataException("adminUser.notification.put.invalid.query");
                    }

                    $userRole = $roleRepository->findOneBy([
                        "name" => "User"
                    ]);

                    $users = $userRepository->getUsersByRole($userRole);

                    foreach ($users as $user) {
                        $notificationBuilder = new NotificationBuilder();

                        $notificationBuilder
                            ->setType($adminUserNotificationPutQuery->getNotificationType())
                            ->setUserAction($adminUserNotificationPutQuery->getNotificationUserType())
                            ->setAction($additionalData["actionId"])
                            ->setUser($user);

                        if (array_key_exists("text", $additionalData)) {
                            $notificationBuilder->setText($additionalData["text"]);
                        }

                        $notification = $notificationBuilder->build();

                        $notificationRepository->add($notification);
                    }
                    break;
                case NotificationType::NEW_AUDIOBOOK:
                    if (!array_key_exists("actionId", $additionalData)) {
                        $endpointLogger->error("Invalid given Query no actionId");
                        throw new InvalidJsonDataException("adminUser.notification.put.invalid.query");
                    }

                    $audiobook = $audiobookRepository->findOneBy([
                        "id" => $additionalData["actionId"]
                    ]);

                    if ($audiobook == null) {
                        $endpointLogger->error("Audiobook dont exist");
                        throw new DataNotFoundException(["adminUser.notification.put.audiobook.dont.exist"]);
                    }

                    $users = $userRepository->getUsersWhereAudiobookInProposed($audiobook);

                    foreach ($users as $user) {
                        $notificationBuilder = new NotificationBuilder();

                        $notificationBuilder
                            ->setType($adminUserNotificationPutQuery->getNotificationType())
                            ->setUserAction($adminUserNotificationPutQuery->getNotificationUserType())
                            ->setAction($additionalData["actionId"])
                            ->setUser($user);

                        if (array_key_exists("text", $additionalData)) {
                            $notificationBuilder->setText($additionalData["text"]);
                        }

                        $notification = $notificationBuilder->build();

                        $notificationRepository->add($notification);
                    }
                    break;
                case NotificationType::USER_DELETE_DECLINE:
                    if (!array_key_exists("actionId", $additionalData)) {
                        $endpointLogger->error("Invalid given Query no actionId");
                        throw new InvalidJsonDataException("adminUser.notification.put.invalid.query");
                    }

                    if (!array_key_exists("userId", $additionalData)) {
                        $endpointLogger->error("Invalid given Query no userId");
                        throw new InvalidJsonDataException("adminUser.notification.put.invalid.query");
                    }

                    $user = $userRepository->findOneBy([
                        "id" => $additionalData["userId"]
                    ]);

                    if ($user == null) {
                        $endpointLogger->error("User dont exist");
                        throw new DataNotFoundException(["adminUser.notification.put.user.dont.exist"]);
                    }

                    $notificationBuilder = new NotificationBuilder();

                    $notificationBuilder
                        ->setType($adminUserNotificationPutQuery->getNotificationType())
                        ->setUserAction($adminUserNotificationPutQuery->getNotificationUserType())
                        ->setAction($additionalData["actionId"])
                        ->setUser($user);

                    if (array_key_exists("text", $additionalData)) {
                        $notificationBuilder->setText($additionalData["text"]);
                    }

                    $notification = $notificationBuilder->build();

                    $notificationRepository->add($notification);

                    break;
            }

            return ResponseTool::getResponse(null, 201);
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminUser.notification.put.invalid.query");
        }

    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param UserRepository $userRepository
     * @param LoggerInterface $endpointLogger
     * @param NotificationRepository $notificationRepository
     * @return Response
     * @throws DataNotFoundException
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
        NotificationRepository         $notificationRepository
    ): Response
    {
        $adminUserNotificationPatchQuery = $requestService->getRequestBodyContent($request, AdminUserNotificationPatchQuery::class);

        if ($adminUserNotificationPatchQuery instanceof AdminUserNotificationPatchQuery) {

            $notification = $notificationRepository->findOneBy([
                "id" => $adminUserNotificationPatchQuery->getNotificationId()
            ]);

            if ($notification == null) {
                $endpointLogger->error("Notification dont exist");
                throw new DataNotFoundException(["adminUser.notification.patch.notification.dont.exist"]);
            }

            $user = $userRepository->findOneBy([
                "id" => $adminUserNotificationPatchQuery->getUserId()
            ]);

            if ($user == null) {
                $endpointLogger->error("User dont exist");
                throw new DataNotFoundException(["adminUser.notification.patch.user.dont.exist"]);
            }
            $notificationBuilder = new NotificationBuilder($notification);

            $notificationBuilder
                ->setType($adminUserNotificationPatchQuery->getNotificationType())
                ->setAction($adminUserNotificationPatchQuery->getActionId())
                ->setUser($user)
                ->setUserAction($adminUserNotificationPatchQuery->getNotificationUserType());

            $additionalData = $adminUserNotificationPatchQuery->getAdditionalData();

            if (array_key_exists("text", $additionalData)) {
                $notificationBuilder->setText($additionalData["text"]);
            }

            $notification = $notificationBuilder->build();

            $notificationRepository->add($notification);

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminUser.notification.patch.invalid.query");
        }
    }
}