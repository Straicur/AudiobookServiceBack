<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Entity\UserDelete;
use App\Entity\UserEdit;
use App\Enums\UserEditType;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\DataNotFoundModel;
use App\Model\JsonDataInvalidModel;
use App\Model\NotAuthorizeModel;
use App\Model\PermissionNotGrantedModel;
use App\Model\UserSettingsGetSuccessModel;
use App\Query\UserResetPasswordConfirmQuery;
use App\Query\UserResetPasswordQuery;
use App\Query\UserSettingsChangeQuery;
use App\Query\UserSettingsEmailQuery;
use App\Query\UserSettingsPasswordQuery;
use App\Repository\AuthenticationTokenRepository;
use App\Repository\UserDeleteRepository;
use App\Repository\UserEditRepository;
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
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * UserController
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
#[OA\Tag(name: "User")]
class UserController extends AbstractController
{
    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserPasswordRepository $userPasswordRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/user/settings/password", name: "userSettingsPassword", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Patch(
        description: "Endpoint is changing password of logged user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserSettingsPasswordQuery::class),
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
    public function userSettingsPassword(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        UserPasswordRepository         $userPasswordRepository,
        TranslateService              $translateService
    ): Response
    {
        $userSettingsPasswordQuery = $requestService->getRequestBodyContent($request, UserSettingsPasswordQuery::class);

        if ($userSettingsPasswordQuery instanceof UserSettingsPasswordQuery) {
            $user = $authorizedUserService->getAuthorizedUser();

            $userPassword = $userPasswordRepository->findOneBy([
                "user" => $user->getId()
            ]);

            $passwordGenerator = new PasswordHashGenerator($userSettingsPasswordQuery->getOldPassword());

            if ($passwordGenerator->generate() != $userPassword->getPassword()) {
                $endpointLogger->error("Password dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserPasswordDontExists")]);
            }

            $newPasswordGenerator = new PasswordHashGenerator($userSettingsPasswordQuery->getNewPassword());

            $userPassword->setPassword($newPasswordGenerator);

            $userPasswordRepository->add($userPassword);

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            $translateService->setPreferredLanguage($request);
            throw new InvalidJsonDataException($translateService);
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserInformationRepository $userInformationRepository
     * @param UserRepository $userRepository
     * @param MailerInterface $mailer
     * @param UserEditRepository $editRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     * @throws TransportExceptionInterface
     */
    #[Route("/api/user/settings/email", name: "userSettingsEmail", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Post(
        description: "Endpoint is sending confirmation email to change user email",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserSettingsEmailQuery::class),
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
    public function userSettingsEmail(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        UserInformationRepository      $userInformationRepository,
        UserRepository                 $userRepository,
        MailerInterface                $mailer,
        UserEditRepository             $editRepository,
        TranslateService              $translateService
    ): Response
    {
        $userSettingsEmailQuery = $requestService->getRequestBodyContent($request, UserSettingsEmailQuery::class);

        if ($userSettingsEmailQuery instanceof UserSettingsEmailQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $userOldEmail = $userInformationRepository->findOneBy([
                "email" => $userSettingsEmailQuery->getOldEmail()
            ]);

            if ($userOldEmail == null) {
                $endpointLogger->error("User dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("EmailDontExists")]);
            }

            $userNewEmail = $userInformationRepository->findOneBy([
                "email" => $userSettingsEmailQuery->getNewEmail()
            ]);

            if ($userNewEmail != null) {
                $endpointLogger->error("User exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("EmailExists")]);
            }

            $userEdit = $editRepository->checkIfUserCanChange($user, UserEditType::EMAIL->value);

            if ($userEdit != null) {
                $endpointLogger->error("User dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserDontExists")]);
            }

            $user->setEdited(true);
            $user->setEditableDate(new \DateTime('Now'));

            $newEditedUser = new UserEdit($user, false, UserEditType::EMAIL->value);
            $newEditedUser->setEditableDate((new \DateTime('Now'))->modify("+10 hour"));

            $editRepository->add($newEditedUser);

            $userRepository->add($user);

            if ($_ENV["APP_ENV"] != "test") {
                $email = (new TemplatedEmail())
                    ->from($_ENV["INSTITUTION_EMAIL"])
                    ->to($user->getUserInformation()->getEmail())
                    ->subject('Zmiana emaila')
                    ->htmlTemplate('emails/userSettingsEmailChange.html.twig')
                    ->context([
                        "userName" => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                        "id" => $user->getId()->__toString(),
                        "userEmail" => $userSettingsEmailQuery->getNewEmail(),
                        "url" => $_ENV["BACKEND_URL"],
                        "lang" => $request->getPreferredLanguage() != null ? $request->getPreferredLanguage() : $translateService->getLocate()
                    ]);
                $mailer->send($email);
            }
            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            $translateService->setPreferredLanguage($request);
            throw new InvalidJsonDataException($translateService);
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserInformationRepository $userInformationRepository
     * @param UserRepository $userRepository
     * @param UserEditRepository $editRepository
     * @return Response
     * @throws DataNotFoundException
     */
    #[Route("/api/user/settings/email/change/{email}/{id}", name: "userSettingsEmailChange", methods: ["GET"])]
    #[AuthValidation(checkAuthToken: false, roles: [])]
    #[OA\Get(
        description: "Endpoint is sending confirmation email to change user email",
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function userSettingsEmailChange(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        UserInformationRepository      $userInformationRepository,
        UserRepository                 $userRepository,
        UserEditRepository             $editRepository,
        TranslateService              $translateService
    ): Response
    {
        $userEmail = $request->get('email');
        $userId = $request->get('id');

        $user = $userRepository->findOneBy([
            "id" => $userId
        ]);

        if ($user == null) {
            $endpointLogger->error("User dont exist");
            $translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$translateService->getTranslation("UserDontExists")]);
        }

        $userEdit = $editRepository->checkIfUserCanChange($user, UserEditType::EMAIL->value);

        if ($userEdit == null) {
            $endpointLogger->error("User dont exist");
            $translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$translateService->getTranslation("UserDontExists")]);
        }

        $userNewEmail = $userInformationRepository->findOneBy([
            "email" => $userEmail
        ]);

        if ($userNewEmail != null) {
            $endpointLogger->error("User exist");
            $translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$translateService->getTranslation("EmailExists")]);
        }

        $userEdit->setEdited(true);

        $editRepository->add($userEdit);

        $userRepository->add($user);

        $userInformation = $user->getUserInformation();

        $userInformation->setEmail($userEmail);

        $userInformationRepository->add($userInformation);

        return $this->render(
            'pages/userSettingsEmailChange.html.twig',
            [
                "url" => $_ENV["FRONTEND_URL"],
                "lang" => $request->getPreferredLanguage() != null ? $request->getPreferredLanguage() : $translateService->getLocate()
            ]
        );
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserDeleteRepository $userDeleteRepository
     * @param AuthenticationTokenRepository $authenticationTokenRepository
     * @param UserRepository $userRepository
     * @param MailerInterface $mailer
     * @return Response
     * @throws DataNotFoundException
     * @throws TransportExceptionInterface
     */
    #[Route("/api/user/settings/delete", name: "userSettingsDelete", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Patch(
        description: "Endpoint is setting user account to not active",
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function userSettingsDelete(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        UserDeleteRepository           $userDeleteRepository,
        AuthenticationTokenRepository  $authenticationTokenRepository,
        UserRepository                 $userRepository,
        MailerInterface                $mailer,
        TranslateService              $translateService
    ): Response
    {
        $user = $authorizedUserService->getAuthorizedUser();

        $userInDelete = $userDeleteRepository->userInList($user);

        if ($userInDelete) {
            $endpointLogger->error("User in list");
            $translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$translateService->getTranslation("UserDeleteExists")]);
        }

        $user->setActive(false);
        $userRepository->add($user, false);

        $activeAuthenticationToken = $authenticationTokenRepository->getLastActiveUserAuthenticationToken($user);

        if ($activeAuthenticationToken != null) {
            $activeAuthenticationToken->setDateExpired((new \DateTime("now"))->modify("-1 day"));
            $authenticationTokenRepository->add($activeAuthenticationToken, false);
        }

        $userDelete = new UserDelete($user);

        $userDeleteRepository->add($userDelete);

        if ($_ENV["APP_ENV"] != "test") {
            $email = (new TemplatedEmail())
                ->from($_ENV["INSTITUTION_EMAIL"])
                ->to($user->getUserInformation()->getEmail())
                ->subject('Prośba o usunięcie konta jest przetwarzana')
                ->htmlTemplate('emails/userDeleteProcessing.html.twig')
                ->context([
                    "userName" => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                    "lang" => $request->getPreferredLanguage() != null ? $request->getPreferredLanguage() : $translateService->getLocate()
                ]);
            $mailer->send($email);
        }

        return ResponseTool::getResponse();
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserInformationRepository $userInformationRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws InvalidJsonDataException
     */
    #[Route("/api/user/settings/change", name: "userSettingsChange", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Patch(
        description: "Endpoint is changing given user informations",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserSettingsChangeQuery::class),
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
    public function userSettingsChange(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        UserInformationRepository      $userInformationRepository,
        TranslateService              $translateService
    ): Response
    {
        $userSettingsChangeQuery = $requestService->getRequestBodyContent($request, UserSettingsChangeQuery::class);

        if ($userSettingsChangeQuery instanceof UserSettingsChangeQuery) {
            $user = $authorizedUserService->getAuthorizedUser();

            $userInformation = $user->getUserInformation();

            $userInformation->setFirstname($userSettingsChangeQuery->getFirstName());
            $userInformation->setLastname($userSettingsChangeQuery->getLastName());
            $userInformation->setPhoneNumber($userSettingsChangeQuery->getPhoneNumber());

            $userInformationRepository->add($userInformation);

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            $translateService->setPreferredLanguage($request);
            throw new InvalidJsonDataException($translateService);
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @return Response
     */
    #[Route("/api/user/settings", name: "userSettingsGet", methods: ["GET"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Patch(
        description: "Endpoint is returning logged user informations",
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: UserSettingsGetSuccessModel::class)
            )
        ]
    )]
    public function userSettingsGet(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
    ): Response
    {
        $user = $authorizedUserService->getAuthorizedUser();

        $userInformation = $user->getUserInformation();

        $successModel = new UserSettingsGetSuccessModel($userInformation->getEmail(), $userInformation->getPhoneNumber(), $userInformation->getFirstname(), $userInformation->getLastname(), $user->getEdited());

        if ($user->getEditableDate() != null) {
            $successModel->setEditableDate($user->getEditableDate());
        }
        return ResponseTool::getResponse($successModel);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param MailerInterface $mailer
     * @param UserInformationRepository $userInformationRepository
     * @param UserRepository $userRepository
     * @param UserEditRepository $editRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     * @throws TransportExceptionInterface'
     */
    #[Route("/api/user/reset/password", name: "userResetPassword", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: false, roles: [])]
    #[OA\Post(
        description: "Endpoint is sending reset password email",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserResetPasswordQuery::class),
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
    public function userResetPassword(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        MailerInterface                $mailer,
        UserInformationRepository      $userInformationRepository,
        UserRepository                 $userRepository,
        UserEditRepository             $editRepository,
        TranslateService              $translateService
    ): Response
    {
        $userResetPasswordQuery = $requestService->getRequestBodyContent($request, UserResetPasswordQuery::class);

        if ($userResetPasswordQuery instanceof UserResetPasswordQuery) {

            $userInformation = $userInformationRepository->findOneBy([
                "email" => $userResetPasswordQuery->getEmail()
            ]);

            if ($userInformation == null) {
                $endpointLogger->error("User dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("EmailDontExists")]);
            }

            $user = $userInformation->getUser();
            $user->setEdited(true);
            $user->setEditableDate(new \DateTime('Now'));

            $editRepository->changeResetPasswordEdits($user);

            $newEditedUser = new UserEdit($user, false, UserEditType::PASSWORD->value);
            $newEditedUser->setEditableDate((new \DateTime('Now'))->modify("+10 hour"));

            $editRepository->add($newEditedUser);

            $userRepository->add($user);

            if ($_ENV["APP_ENV"] != "test") {
                $email = (new TemplatedEmail())
                    ->from($_ENV["INSTITUTION_EMAIL"])
                    ->to($user->getUserInformation()->getEmail())
                    ->subject('Reset hasła')
                    ->htmlTemplate('emails/userSettingsResetPassword.html.twig')
                    ->context([
                        "userName" => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                        "id" => $user->getId()->__toString(),
                        "url" => $_ENV["FRONTEND_URL"],
                        "lang" => $request->getPreferredLanguage() != null ? $request->getPreferredLanguage() : $translateService->getLocate()
                    ]);
                $mailer->send($email);
            }

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            $translateService->setPreferredLanguage($request);
            throw new InvalidJsonDataException($translateService);
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @param UserPasswordRepository $userPasswordRepository
     * @param UserEditRepository $editRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/user/reset/password/confirm", name: "userResetPasswordConfirm", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: false, roles: [])]
    #[OA\Patch(
        description: "Endpoint is changing user password",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserResetPasswordConfirmQuery::class),
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
    public function userResetPasswordConfirm(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        UserRepository                 $userRepository,
        UserPasswordRepository         $userPasswordRepository,
        UserEditRepository             $editRepository,
        TranslateService              $translateService
    ): Response
    {
        $userResetPasswordConfirmQuery = $requestService->getRequestBodyContent($request, UserResetPasswordConfirmQuery::class);

        if ($userResetPasswordConfirmQuery instanceof UserResetPasswordConfirmQuery) {

            $user = $userRepository->findOneBy([
                "id" => $userResetPasswordConfirmQuery->getUserId()
            ]);

            if ($user == null) {
                $endpointLogger->error("User dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("EmailDontExists")]);
            }

            $userEdit = $editRepository->checkIfUserCanChange($user, UserEditType::PASSWORD->value);

            if ($userEdit == null) {
                $endpointLogger->error("User dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("EmailDontExists")]);
            }

            $userEdit->setEdited(true);

            $editRepository->add($userEdit);

            $userRepository->add($user);

            $password = $userPasswordRepository->findOneBy([
                "user" => $user->getId()
            ]);

            $passwordGenerator = new PasswordHashGenerator($userResetPasswordConfirmQuery->getPassword());

            $password->setPassword($passwordGenerator);

            $userPasswordRepository->add($password);

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            $translateService->setPreferredLanguage($request);
            throw new InvalidJsonDataException($translateService);
        }
    }
    #[Route("/api/report", name: "apiReport", methods: ["GET"])]
    #[OA\Get(
        description: "Method used to report for not loged users",
        security: [],
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            ),
        ]
    )]
    public function report(
        Request                   $request,
        LoggerInterface           $usersLogger,
        LoggerInterface           $endpointLogger,
        TranslateService          $translateService
    ): Response
    {
        return ResponseTool::getResponse();
    }
}