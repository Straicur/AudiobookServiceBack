<?php

declare(strict_types = 1);

namespace App\Controller\User;

use App\Annotation\AuthValidation;
use App\Entity\UserDelete;
use App\Entity\UserEdit;
use App\Entity\UserParentalControlCode;
use App\Enums\Cache\UserStockCacheTags;
use App\Enums\UserEditType;
use App\Enums\UserRolesNames;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Model\User\UserChangeCodeSuccessModel;
use App\Model\User\UserSettingsGetSuccessModel;
use App\Query\User\UserParentControlPatchQuery;
use App\Query\User\UserResetPasswordConfirmQuery;
use App\Query\User\UserResetPasswordQuery;
use App\Query\User\UserSettingsChangeQuery;
use App\Query\User\UserSettingsEmailQuery;
use App\Query\User\UserSettingsPasswordQuery;
use App\Repository\AuthenticationTokenRepository;
use App\Repository\UserDeleteRepository;
use App\Repository\UserEditRepository;
use App\Repository\UserInformationRepository;
use App\Repository\UserParentalControlCodeRepository;
use App\Repository\UserPasswordRepository;
use App\Repository\UserRepository;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Service\SmsService;
use App\Service\TranslateServiceInterface;
use App\Tool\ResponseTool;
use App\ValueGenerator\PasswordHashGenerator;
use App\ValueGenerator\UserEditConfirmGenerator;
use App\ValueGenerator\UserParentalControlCodeGenerator;
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
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Throwable;

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
#[OA\Tag(name: 'UserSettings')]
class UserSettingsController extends AbstractController
{
    public function __construct(
        private readonly RequestServiceInterface $requestService,
        private readonly AuthorizedUserServiceInterface $authorizedUserService,
        private readonly LoggerInterface $endpointLogger,
        private readonly UserPasswordRepository $userPasswordRepository,
        private readonly TranslateServiceInterface $translateService,
        private readonly AuthenticationTokenRepository $authenticationTokenRepository,
        private readonly UserEditRepository $userEditRepository,
        private readonly UserEditRepository $editRepository,
        private readonly MailerInterface $mailer,
        private readonly UserRepository $userRepository,
        private readonly UserInformationRepository $userInformationRepository,
        private readonly UserDeleteRepository $userDeleteRepository,
        private readonly UserParentalControlCodeRepository $controlCodeRepository,
        private readonly TagAwareCacheInterface $stockCache,
        private readonly SmsService $smsTool,
        #[Autowire(env: 'INSTITUTION_EMAIL')] private readonly string $institutionEmail,
        #[Autowire(env: 'BACKEND_URL')] private readonly string $backendUrl,
        #[Autowire(env: 'FRONTEND_URL')] private readonly string $frontendUrl,
    ) {}

    #[Route('/api/user/settings/password', name: 'userSettingsPassword', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Patch(
        description: 'Endpoint is changing password of logged user',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserSettingsPasswordQuery::class),
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
    public function userSettingsPassword(
        Request $request,
    ): Response {
        $userSettingsPasswordQuery = $this->requestService->getRequestBodyContent($request, UserSettingsPasswordQuery::class);

        if ($userSettingsPasswordQuery instanceof UserSettingsPasswordQuery) {
            $user = $this->authorizedUserService::getAuthorizedUser();

            $userPassword = $this->userPasswordRepository->findOneBy([
                'user' => $user->getId(),
            ]);

            $passwordGenerator = new PasswordHashGenerator($userSettingsPasswordQuery->getOldPassword());

            if (null === $userPassword || $passwordGenerator->generate() !== $userPassword->getPassword()) {
                $this->endpointLogger->error('Password dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserPasswordDontExists')]);
            }

            $userEdit = $this->userEditRepository->checkIfUserCanChangeWithCode($user, UserEditType::PASSWORD, $userSettingsPasswordQuery->getCode());

            if (null === $userEdit) {
                $this->endpointLogger->error('User changed password');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('IncorrectCodeOrPasswordWasChanged')]);
            }

            $userEdit->setEdited(true);
            $this->userEditRepository->add($userEdit);

            $newPasswordGenerator = new PasswordHashGenerator($userSettingsPasswordQuery->getNewPassword());

            $userPassword->setPassword($newPasswordGenerator);

            $this->userPasswordRepository->add($userPassword);

            $authToken = $this->authenticationTokenRepository->getLastActiveUserAuthenticationToken($user);

            if (null !== $authToken) {
                $authToken->setDateExpired(new DateTime()->modify('-1 day'));
                $this->authenticationTokenRepository->add($authToken);
            }

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/user/settings/password/code', name: 'userSettingsPasswordCode', methods: ['PUT'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Put(
        description: 'Endpoint is sending new password code to email',
        requestBody: new OA\RequestBody(),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
            ),
        ]
    )]
    public function userSettingsPasswordCode(
        Request $request,
    ): Response {
        $user = $this->authorizedUserService::getAuthorizedUser();

        $userEdit = $this->editRepository->checkIfUserCanChange($user, UserEditType::PASSWORD);

        if (null !== $userEdit) {
            $this->endpointLogger->error('User changed password');
            $this->translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$this->translateService->getTranslation('UserChangedPassword')]);
        }

        $newEditedUser = new UserEdit($user, false, UserEditType::PASSWORD);
        $newEditedUser
            ->setCode(new UserEditConfirmGenerator())
            ->setEditableDate(new DateTime()->modify('+15 minutes'));

        $this->editRepository->add($newEditedUser);

        if ('test' !== $_ENV['APP_ENV']) {
            $email = new TemplatedEmail()
                ->from($this->institutionEmail)
                ->to($user->getUserInformation()->getEmail())
                ->subject($this->translateService->getTranslation('ChangePasswordSubject'))
                ->htmlTemplate('emails/userSettingsPasswordChangeCode.html.twig')
                ->context([
                    'userName' => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                    'code'     => $newEditedUser->getCode(),
                    'lang'     => $request->getPreferredLanguage() ?? $this->translateService->getLocate(),
                ]);
            $this->mailer->send($email);
        }

        return ResponseTool::getResponse(new UserChangeCodeSuccessModel($newEditedUser->getCode()), Response::HTTP_CREATED);
    }

    #[Route('/api/user/settings/email/smsCode', name: 'userSettingsEmailSmsCode', methods: ['PUT'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Put(
        description: 'Endpoint is sending confirmation sms code for changing email',
        requestBody: new OA\RequestBody(),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
            ),
        ]
    )]
    public function userSettingsEmailSmsCode(
        Request $request,
    ): Response {
        $user = $this->authorizedUserService::getAuthorizedUser();

        $userEdit = $this->editRepository->checkIfUserCanChange($user, UserEditType::EMAIL_CODE);

        if (null !== $userEdit) {
            $this->endpointLogger->error('User changed password');
            $this->translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$this->translateService->getTranslation('UserChangedEmail')]);
        }

        $newEditedUser = new UserEdit($user, false, UserEditType::EMAIL_CODE);
        $newEditedUser
            ->setCode(new UserEditConfirmGenerator())
            ->setEditableDate(new DateTime()->modify('+15 minutes'));

        $this->editRepository->add($newEditedUser);

        try {
            $status = $this->smsTool->sendSms($user->getUserInformation()->getPhoneNumber(), $this->translateService->getTranslation('SmsCodeContent') . ': ' . $newEditedUser->getCode() . ' ');
        } catch (Throwable $e) {
            $this->endpointLogger->error($e->getMessage());
            $this->translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$this->translateService->getTranslation('SmsCodeError')]);
        }

        if (!$status) {
            $this->endpointLogger->error("Can't send sms");
            $this->translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$this->translateService->getTranslation('SmsCodeError')]);
        }

        $this->userRepository->add($user);

        return ResponseTool::getResponse(new UserChangeCodeSuccessModel($newEditedUser->getCode()), Response::HTTP_CREATED);
    }

    #[Route('/api/user/settings/email', name: 'userSettingsEmail', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Post(
        description: 'Endpoint is sending confirmation email to change user email',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserSettingsEmailQuery::class),
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
    public function userSettingsEmail(
        Request $request,
    ): Response {
        $userSettingsEmailQuery = $this->requestService->getRequestBodyContent($request, UserSettingsEmailQuery::class);

        if ($userSettingsEmailQuery instanceof UserSettingsEmailQuery) {
            $user = $this->authorizedUserService::getAuthorizedUser();

            $userOldEmail = $this->userInformationRepository->findOneBy([
                'email' => $userSettingsEmailQuery->getOldEmail(),
            ]);

            if (null === $userOldEmail) {
                $this->endpointLogger->error('User dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('EmailDontExists')]);
            }

            $userNewEmail = $this->userInformationRepository->findOneBy([
                'email' => $userSettingsEmailQuery->getNewEmail(),
            ]);

            if (null !== $userNewEmail) {
                $this->endpointLogger->error('User exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('EmailExists')]);
            }

            $userEditCode = $this->editRepository->checkIfUserCanChangeWithCode($user, UserEditType::EMAIL_CODE, $userSettingsEmailQuery->getCode());

            if (null === $userEditCode) {
                $this->endpointLogger->error('User changed password');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('IncorrectCodeOrEmailWasChanged')]);
            }

            $userEdit = $this->editRepository->checkIfUserCanChange($user, UserEditType::EMAIL);

            if (null !== $userEdit) {
                $this->endpointLogger->error('User changed password');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserChangedEmail')]);
            }

            $user
                ->setEdited(true)
                ->setEditableDate(new DateTime());

            $newEditedUser = new UserEdit($user, false, UserEditType::EMAIL);
            $newEditedUser->setEditableDate(new DateTime()->modify('+10 hour'));

            $this->editRepository->add($newEditedUser, false);

            $userEditCode->setEdited(true);
            $this->editRepository->add($userEditCode, false);

            $this->userRepository->add($user);

            if ('test' !== $_ENV['APP_ENV']) {
                $email = new TemplatedEmail()
                    ->from($this->institutionEmail)
                    ->to($user->getUserInformation()->getEmail())
                    ->subject($this->translateService->getTranslation('ChangeEmailSubject'))
                    ->htmlTemplate('emails/userSettingsEmailChange.html.twig')
                    ->context([
                        'userName'  => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                        'id'        => $user->getId()->__toString(),
                        'userEmail' => $userSettingsEmailQuery->getNewEmail(),
                        'url'       => $this->backendUrl,
                        'lang'      => $request->getPreferredLanguage() ?? $this->translateService->getLocate(),
                    ]);
                $this->mailer->send($email);
            }

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/user/settings/email/change/{email}/{id}', name: 'userSettingsEmailChange', methods: ['GET'])]
    #[OA\Get(
        description: 'Endpoint is sending confirmation email to change user email',
        requestBody: new OA\RequestBody(),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
            ),
        ]
    )]
    public function userSettingsEmailChange(
        Request $request,
    ): Response {
        $userEmail = $request->get('email');
        $userId = $request->get('id');

        $user = $this->userRepository->find($userId);

        if (null === $user) {
            $this->endpointLogger->error('User dont exist');
            $this->translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$this->translateService->getTranslation('UserDontExists')]);
        }

        $userEdit = $this->editRepository->checkIfUserCanChange($user, UserEditType::EMAIL);

        if (null === $userEdit) {
            $this->endpointLogger->error('User changed password');
            $this->translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$this->translateService->getTranslation('UserChangedPassword')]);
        }

        $userNewEmail = $this->userInformationRepository->findOneBy([
            'email' => $userEmail,
        ]);

        if (null !== $userNewEmail) {
            $this->endpointLogger->error('User exist');
            $this->translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$this->translateService->getTranslation('EmailExists')]);
        }

        $userEdit->setEdited(true);

        $this->editRepository->add($userEdit);

        $this->userRepository->add($user);

        $userInformation = $user->getUserInformation();

        $userInformation->setEmail($userEmail);

        $this->userInformationRepository->add($userInformation);

        $authToken = $this->authenticationTokenRepository->getLastActiveUserAuthenticationToken($user);

        if (null !== $authToken) {
            $authToken->setDateExpired(new DateTime()->modify('-1 day'));
            $this->authenticationTokenRepository->add($authToken);
        }

        return $this->render(
            'pages/userSettingsEmailChange.html.twig',
            [
                'url'  => $this->frontendUrl,
                'lang' => $request->getPreferredLanguage() ?? $this->translateService->getLocate(),
            ],
        );
    }

    #[Route('/api/user/settings/delete', name: 'userSettingsDelete', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Patch(
        description: 'Endpoint is setting user account to not active',
        requestBody: new OA\RequestBody(),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
            ),
        ]
    )]
    public function userSettingsDelete(
        Request $request,
    ): Response {
        $user = $this->authorizedUserService::getAuthorizedUser();

        $userInDelete = $this->userDeleteRepository->userInList($user);

        if ($userInDelete) {
            $this->endpointLogger->error('User in list');
            $this->translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$this->translateService->getTranslation('UserDeleteExists')]);
        }

        $user->setActive(false);
        $this->userRepository->add($user, false);

        $activeAuthenticationToken = $this->authenticationTokenRepository->getLastActiveUserAuthenticationToken($user);

        if (null !== $activeAuthenticationToken) {
            $activeAuthenticationToken->setDateExpired(new DateTime()->modify('-1 day'));
            $this->authenticationTokenRepository->add($activeAuthenticationToken, false);
        }

        $userDelete = new UserDelete($user);

        $this->userDeleteRepository->add($userDelete);

        if ('test' !== $_ENV['APP_ENV']) {
            $email = new TemplatedEmail()
                ->from($this->institutionEmail)
                ->to($user->getUserInformation()->getEmail())
                ->subject($this->translateService->getTranslation('RequestDeleteAccountSubject'))
                ->htmlTemplate('emails/userDeleteProcessing.html.twig')
                ->context([
                    'userName' => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                    'lang'     => $request->getPreferredLanguage() ?? $this->translateService->getLocate(),
                ]);
            $this->mailer->send($email);
        }

        return ResponseTool::getResponse();
    }

    #[Route('/api/user/settings/change/code', name: 'userSettingsChangeCode', methods: ['PUT'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Put(
        description: 'Endpoint is sending email code to change user information',
        requestBody: new OA\RequestBody(),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
            ),
        ]
    )]
    public function userSettingsChangeCode(
        Request $request,
    ): Response {
        $user = $this->authorizedUserService::getAuthorizedUser();

        $userEdit = $this->editRepository->checkIfUserCanChange($user, UserEditType::USER_DATA);

        if (null !== $userEdit) {
            $this->endpointLogger->error('User changed password');
            $this->translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$this->translateService->getTranslation('UserChangedUserData')]);
        }

        $newEditedUser = new UserEdit($user, false, UserEditType::USER_DATA);
        $newEditedUser
            ->setCode(new UserEditConfirmGenerator())
            ->setEditableDate(new DateTime()->modify('+15 minutes'));

        $this->editRepository->add($newEditedUser);

        if ('test' !== $_ENV['APP_ENV']) {
            $email = new TemplatedEmail()
                ->from($this->institutionEmail)
                ->to($user->getUserInformation()->getEmail())
                ->subject($this->translateService->getTranslation('ChangeUserDataSubject'))
                ->htmlTemplate('emails/userSettingsChangeCode.html.twig')
                ->context([
                    'userName' => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                    'code'     => $newEditedUser->getCode(),
                    'lang'     => $request->getPreferredLanguage() ?? $this->translateService->getLocate(),
                ]);
            $this->mailer->send($email);
        }

        return ResponseTool::getResponse(new UserChangeCodeSuccessModel($newEditedUser->getCode()), Response::HTTP_CREATED);
    }

    #[Route('/api/user/settings/change', name: 'userSettingsChange', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Patch(
        description: 'Endpoint is changing given user informations',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserSettingsChangeQuery::class),
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
    public function userSettingsChange(
        Request $request,
    ): Response {
        $userSettingsChangeQuery = $this->requestService->getRequestBodyContent($request, UserSettingsChangeQuery::class);

        if ($userSettingsChangeQuery instanceof UserSettingsChangeQuery) {
            $user = $this->authorizedUserService::getAuthorizedUser();

            $userEdit = $this->editRepository->checkIfUserCanChangeWithCode($user, UserEditType::USER_DATA, $userSettingsChangeQuery->getCode());

            if (null === $userEdit) {
                $this->endpointLogger->error('User changed password');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('IncorrectCodeOrUserDataWasChanged')]);
            }

            $userEdit->setEdited(true);

            $userInformation = $user->getUserInformation();

            $userInformation
                ->setFirstname($userSettingsChangeQuery->getFirstName())
                ->setLastname($userSettingsChangeQuery->getLastName());

            if ($userInformation->getPhoneNumber() !== $userSettingsChangeQuery->getPhoneNumber()) {
                $existingPhone = $this->userInformationRepository->findOneBy([
                    'phoneNumber' => $userSettingsChangeQuery->getPhoneNumber(),
                ]);

                if (null !== $existingPhone) {
                    $this->endpointLogger->error('Phone number already exists');
                    $this->translateService->setPreferredLanguage($request);
                    throw new DataNotFoundException([$this->translateService->getTranslation('PhoneNumberExists')]);
                }

                $userInformation->setPhoneNumber($userSettingsChangeQuery->getPhoneNumber());
            }

            $this->editRepository->add($userEdit, false);
            $this->userInformationRepository->add($userInformation);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/user/settings', name: 'userSettingsGet', methods: ['GET'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Get(
        description: 'Endpoint is returning logged user informations',
        requestBody: new OA\RequestBody(),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: UserSettingsGetSuccessModel::class),
            ),
        ]
    )]
    public function userSettingsGet(): Response
    {
        $user = $this->authorizedUserService::getAuthorizedUser();
        $userInformation = $user->getUserInformation();
        $successModel = new UserSettingsGetSuccessModel($userInformation->getEmail(), $userInformation->getPhoneNumber(), $userInformation->getFirstname(), $userInformation->getLastname(), $user->getEdited());
        if ($user->getEditableDate() !== null) {
            $successModel->setEditableDate($user->getEditableDate());
        }

        if ($userInformation->getBirthday() !== null) {
            $successModel->setBirthday($userInformation->getBirthday());
        }

        return ResponseTool::getResponse($successModel);
    }

    #[Route('/api/user/reset/password', name: 'userResetPassword', methods: ['POST'])]
    #[OA\Post(
        description: 'Endpoint is sending reset password email',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserResetPasswordQuery::class),
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
    public function userResetPassword(
        Request $request,
    ): Response {
        $userResetPasswordQuery = $this->requestService->getRequestBodyContent($request, UserResetPasswordQuery::class);

        if ($userResetPasswordQuery instanceof UserResetPasswordQuery) {
            $userInformation = $this->userInformationRepository->findOneBy([
                'email' => $userResetPasswordQuery->getEmail(),
            ]);

            if (null === $userInformation) {
                $this->endpointLogger->error('User dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('EmailDontExists')]);
            }

            $user = $userInformation
                ->getUser()
                ->setEdited(true)
                ->setEditableDate(new DateTime());

            $this->editRepository->changeResetPasswordEdits($user);

            $newEditedUser = new UserEdit($user, false, UserEditType::PASSWORD_RESET);
            $newEditedUser->setEditableDate(new DateTime()->modify('+10 hour'));

            $this->editRepository->add($newEditedUser);

            $this->userRepository->add($user);

            if ('test' !== $_ENV['APP_ENV']) {
                $email = new TemplatedEmail()
                    ->from($this->institutionEmail)
                    ->to($user->getUserInformation()->getEmail())
                    ->subject($this->translateService->getTranslation('PasswordResetSubject'))
                    ->htmlTemplate('emails/userSettingsResetPassword.html.twig')
                    ->context([
                        'userName' => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                        'id'       => $user->getId()->__toString(),
                        'url'      => $this->frontendUrl,
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

    #[Route('/api/user/reset/password/confirm', name: 'userResetPasswordConfirm', methods: ['PATCH'])]
    #[OA\Patch(
        description: 'Endpoint is changing user password',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserResetPasswordConfirmQuery::class),
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
    public function userResetPasswordConfirm(
        Request $request,
    ): Response {
        $userResetPasswordConfirmQuery = $this->requestService->getRequestBodyContent($request, UserResetPasswordConfirmQuery::class);

        if ($userResetPasswordConfirmQuery instanceof UserResetPasswordConfirmQuery) {
            $user = $this->userRepository->find($userResetPasswordConfirmQuery->getUserId());

            if (null === $user) {
                $this->endpointLogger->error('User dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('EmailDontExists')]);
            }

            $userEdit = $this->editRepository->checkIfUserCanChange($user, UserEditType::PASSWORD_RESET);

            if (null === $userEdit) {
                $this->endpointLogger->error('User changed password');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserChangedPassword')]);
            }

            $userEdit->setEdited(true);

            $this->editRepository->add($userEdit);

            $this->userRepository->add($user);

            $password = $this->userPasswordRepository->findOneBy([
                'user' => $user->getId(),
            ]);

            $passwordGenerator = new PasswordHashGenerator($userResetPasswordConfirmQuery->getPassword());

            $password->setPassword($passwordGenerator);

            $this->userPasswordRepository->add($password);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/user/parent/control', name: 'userParentControlPut', methods: ['PUT'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Put(
        description: 'Endpoint is creating a sms code for changing parent control settings',
        requestBody: new OA\RequestBody(),
        responses  : [
            new OA\Response(
                response   : 201,
                description: 'Success',
                content    : new Model(type: UserChangeCodeSuccessModel::class),
            ),
        ]
    )]
    public function userParentControlPut(
        Request $request,
    ): Response {
        $user = $this->authorizedUserService::getAuthorizedUser();

        $lastWeakAttempts = $this->controlCodeRepository->getUserParentalControlCodeFromLastWeekByUser($user);

        if (3 <= $lastWeakAttempts) {
            $this->endpointLogger->error('To many attempts to get UserParentalControlCode sms code');
            $this->translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$this->translateService->getTranslation('UserParentalControlCodeToManyAttempts')]);
        }

        $this->controlCodeRepository->setCodesToNotActive($user);

        $newGenerator = new UserParentalControlCodeGenerator();

        $newUserParentalControlCode = new UserParentalControlCode($user, $newGenerator);

        $this->controlCodeRepository->add($newUserParentalControlCode, false);

        try {
            $status = $this->smsTool->sendSms($user->getUserInformation()->getPhoneNumber(), $this->translateService->getTranslation('SmsCodeContent') . ': ' . $newUserParentalControlCode->getCode() . ' ');
        } catch (Throwable $e) {
            $this->endpointLogger->error($e->getMessage());
            $this->translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$this->translateService->getTranslation('SmsCodeError')]);
        }

        if (!$status) {
            $this->endpointLogger->error("Can't send sms");
            $this->translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$this->translateService->getTranslation('SmsCodeError')]);
        }

        $this->userRepository->add($user);

        return ResponseTool::getResponse(new UserChangeCodeSuccessModel($newUserParentalControlCode->getCode()), 201);
    }

    #[Route('/api/user/parent/control', name: 'userParentControlPatch', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Patch(
        description: 'Endpoint is changing parent control settings',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserParentControlPatchQuery::class),
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
    public function userParentControlPatch(
        Request $request,
    ): Response {
        $userParentControlPatchQuery = $this->requestService->getRequestBodyContent($request, UserParentControlPatchQuery::class);

        if ($userParentControlPatchQuery instanceof UserParentControlPatchQuery) {
            $user = $this->authorizedUserService::getAuthorizedUser();

            $controlCode = $this->controlCodeRepository->findOneBy([
                'code'   => $userParentControlPatchQuery->getSmsCode(),
                'active' => true,
                'user'   => $user->getId(),
            ]);

            if (null === $controlCode) {
                $this->endpointLogger->error('UserParentalControlCode dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserParentalControlCodeDontExists')]);
            }

            $additionalData = $userParentControlPatchQuery->getAdditionalData();
            $userInformation = $user->getUserInformation();

            $birthday = $additionalData['birthday'] ?? null;

            if (null !== $birthday) {
                $userInformation->setBirthday($birthday);
            } else {
                $userInformation->setBirthday(null);
            }

            $this->userInformationRepository->add($userInformation);

            $controlCode->setActive(false);
            $this->controlCodeRepository->add($controlCode);

            if ('test' !== $_ENV['APP_ENV']) {
                $email = new TemplatedEmail()
                    ->from($this->institutionEmail)
                    ->to($user->getUserInformation()->getEmail())
                    ->subject($this->translateService->getTranslation('ParentControlChangedSubject'))
                    ->htmlTemplate('emails/userParentControlChanged.html.twig')
                    ->context([
                        'name'   => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                        'change' => null !== $birthday,
                        'lang'   => $request->getPreferredLanguage() ?? $this->translateService->getLocate(),
                    ]);
                $this->mailer->send($email);
            }

            $this->stockCache->invalidateTags([
                UserStockCacheTags::USER_AUDIOBOOKS->value,
                UserStockCacheTags::USER_PROPOSED_AUDIOBOOKS->value,
                UserStockCacheTags::USER_CATEGORIES_TREE->value,
            ]);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }
}
