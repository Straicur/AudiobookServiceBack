<?php

declare(strict_types=1);

namespace App\Controller;

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
use App\Service\TranslateService;
use App\Tool\ResponseTool;
use App\Tool\SmsTool;
use App\ValueGenerator\PasswordHashGenerator;
use App\ValueGenerator\UserEditConfirmGenerator;
use App\ValueGenerator\UserParentalControlCodeGenerator;
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
#[OA\Tag(name: 'User')]
class UserSettingsController extends AbstractController
{
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
        RequestServiceInterface $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface $endpointLogger,
        UserPasswordRepository $userPasswordRepository,
        TranslateService $translateService,
        AuthenticationTokenRepository $authenticationTokenRepository,
        UserEditRepository $userEditRepository,
    ): Response {
        $userSettingsPasswordQuery = $requestService->getRequestBodyContent($request, UserSettingsPasswordQuery::class);

        if ($userSettingsPasswordQuery instanceof UserSettingsPasswordQuery) {
            $user = $authorizedUserService::getAuthorizedUser();

            $userPassword = $userPasswordRepository->findOneBy([
                'user' => $user->getId(),
            ]);

            $passwordGenerator = new PasswordHashGenerator($userSettingsPasswordQuery->getOldPassword());

            if ($userPassword === null || $passwordGenerator->generate() !== $userPassword->getPassword()) {
                $endpointLogger->error('Password dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserPasswordDontExists')]);
            }

            $userEdit = $userEditRepository->checkIfUserCanChangeWithCode($user, UserEditType::PASSWORD, $userSettingsPasswordQuery->getCode());

            if ($userEdit === null) {
                $endpointLogger->error('User changed password');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('IncorrectCodeOrPasswordWasChanged')]);
            }

            $userEdit->setEdited(true);
            $userEditRepository->add($userEdit);

            $newPasswordGenerator = new PasswordHashGenerator($userSettingsPasswordQuery->getNewPassword());

            $userPassword->setPassword($newPasswordGenerator);

            $userPasswordRepository->add($userPassword);

            $authToken = $authenticationTokenRepository->getLastActiveUserAuthenticationToken($user);

            if ($authToken !== null) {
                $authToken->setDateExpired((new DateTime())->modify('-1 day'));
                $authenticationTokenRepository->add($authToken);
            }

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
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
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface $endpointLogger,
        UserEditRepository $editRepository,
        TranslateService $translateService,
        MailerInterface $mailer,
    ): Response {
        $user = $authorizedUserService::getAuthorizedUser();

        $userEdit = $editRepository->checkIfUserCanChange($user, UserEditType::PASSWORD);

        if ($userEdit !== null) {
            $endpointLogger->error('User changed password');
            $translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$translateService->getTranslation('UserChangedPassword')]);
        }

        $newEditedUser = new UserEdit($user, false, UserEditType::PASSWORD);
        $newEditedUser
            ->setCode(new UserEditConfirmGenerator())
            ->setEditableDate((new DateTime())->modify('+15 minutes'));

        $editRepository->add($newEditedUser);

        if ($_ENV['APP_ENV'] !== 'test') {
            $email = (new TemplatedEmail())
                ->from($_ENV['INSTITUTION_EMAIL'])
                ->to($user->getUserInformation()->getEmail())
                ->subject($translateService->getTranslation('ChangePasswordSubject'))
                ->htmlTemplate('emails/userSettingsPasswordChangeCode.html.twig')
                ->context([
                    'userName' => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                    'code'     => $newEditedUser->getCode(),
                    'lang'     => $request->getPreferredLanguage() !== null ? $request->getPreferredLanguage() : $translateService->getLocate(),
                ]);
            $mailer->send($email);
        }

        return ResponseTool::getResponse(new UserChangeCodeSuccessModel($newEditedUser->getCode()), 201);
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
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface $endpointLogger,
        UserRepository $userRepository,
        UserEditRepository $editRepository,
        TranslateService $translateService,
    ): Response {
        $user = $authorizedUserService::getAuthorizedUser();

        $userEdit = $editRepository->checkIfUserCanChange($user, UserEditType::EMAIL_CODE);

        if ($userEdit !== null) {
            $endpointLogger->error('User changed password');
            $translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$translateService->getTranslation('UserChangedEmail')]);
        }

        $newEditedUser = new UserEdit($user, false, UserEditType::EMAIL_CODE);
        $newEditedUser
            ->setCode(new UserEditConfirmGenerator())
            ->setEditableDate((new DateTime())->modify('+15 minutes'));

        $editRepository->add($newEditedUser);

        $smsTool = new SmsTool();

        try {
            $status = $smsTool->sendSms($user->getUserInformation()->getPhoneNumber(), $translateService->getTranslation('SmsCodeContent') . ': ' . $newEditedUser->getCode() . ' ');
        } catch (Throwable $e) {
            $endpointLogger->error($e->getMessage());
            $translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$translateService->getTranslation('SmsCodeError')]);
        }

        if (!$status) {
            $endpointLogger->error("Can't send sms");
            $translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$translateService->getTranslation('SmsCodeError')]);
        }

        $userRepository->add($user);

        return ResponseTool::getResponse(new UserChangeCodeSuccessModel($newEditedUser->getCode()), 201);
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
        RequestServiceInterface $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface $endpointLogger,
        UserInformationRepository $userInformationRepository,
        UserRepository $userRepository,
        MailerInterface $mailer,
        UserEditRepository $editRepository,
        TranslateService $translateService,
    ): Response {
        $userSettingsEmailQuery = $requestService->getRequestBodyContent($request, UserSettingsEmailQuery::class);

        if ($userSettingsEmailQuery instanceof UserSettingsEmailQuery) {
            $user = $authorizedUserService::getAuthorizedUser();

            $userOldEmail = $userInformationRepository->findOneBy([
                'email' => $userSettingsEmailQuery->getOldEmail(),
            ]);

            if ($userOldEmail === null) {
                $endpointLogger->error('User dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('EmailDontExists')]);
            }

            $userNewEmail = $userInformationRepository->findOneBy([
                'email' => $userSettingsEmailQuery->getNewEmail(),
            ]);

            if ($userNewEmail !== null) {
                $endpointLogger->error('User exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('EmailExists')]);
            }

            $userEditCode = $editRepository->checkIfUserCanChangeWithCode($user, UserEditType::EMAIL_CODE, $userSettingsEmailQuery->getCode());

            if ($userEditCode === null) {
                $endpointLogger->error('User changed password');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('IncorrectCodeOrEmailWasChanged')]);
            }

            $userEdit = $editRepository->checkIfUserCanChange($user, UserEditType::EMAIL);

            if ($userEdit !== null) {
                $endpointLogger->error('User changed password');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserChangedEmail')]);
            }

            $user
                ->setEdited(true)
                ->setEditableDate(new DateTime());

            $newEditedUser = new UserEdit($user, false, UserEditType::EMAIL);
            $newEditedUser->setEditableDate((new DateTime())->modify('+10 hour'));

            $editRepository->add($newEditedUser, false);

            $userEditCode->setEdited(true);
            $editRepository->add($userEditCode, false);

            $userRepository->add($user);

            if ($_ENV['APP_ENV'] !== 'test') {
                $email = (new TemplatedEmail())
                    ->from($_ENV['INSTITUTION_EMAIL'])
                    ->to($user->getUserInformation()->getEmail())
                    ->subject($translateService->getTranslation('ChangeEmailSubject'))
                    ->htmlTemplate('emails/userSettingsEmailChange.html.twig')
                    ->context([
                        'userName'  => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                        'id'        => $user->getId()->__toString(),
                        'userEmail' => $userSettingsEmailQuery->getNewEmail(),
                        'url'       => $_ENV['BACKEND_URL'],
                        'lang'      => $request->getPreferredLanguage() !== null ? $request->getPreferredLanguage() : $translateService->getLocate(),
                    ]);
                $mailer->send($email);
            }
            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
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
        LoggerInterface $endpointLogger,
        UserInformationRepository $userInformationRepository,
        UserRepository $userRepository,
        UserEditRepository $editRepository,
        AuthenticationTokenRepository $authenticationTokenRepository,
        TranslateService $translateService,
    ): Response {
        $userEmail = $request->get('email');
        $userId = $request->get('id');

        $user = $userRepository->find($userId);

        if ($user === null) {
            $endpointLogger->error('User dont exist');
            $translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$translateService->getTranslation('UserDontExists')]);
        }

        $userEdit = $editRepository->checkIfUserCanChange($user, UserEditType::EMAIL);

        if ($userEdit === null) {
            $endpointLogger->error('User changed password');
            $translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$translateService->getTranslation('UserChangedPassword')]);
        }

        $userNewEmail = $userInformationRepository->findOneBy([
            'email' => $userEmail,
        ]);

        if ($userNewEmail !== null) {
            $endpointLogger->error('User exist');
            $translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$translateService->getTranslation('EmailExists')]);
        }

        $userEdit->setEdited(true);

        $editRepository->add($userEdit);

        $userRepository->add($user);

        $userInformation = $user->getUserInformation();

        $userInformation->setEmail($userEmail);

        $userInformationRepository->add($userInformation);

        $authToken = $authenticationTokenRepository->getLastActiveUserAuthenticationToken($user);

        if ($authToken !== null) {
            $authToken->setDateExpired((new DateTime())->modify('-1 day'));
            $authenticationTokenRepository->add($authToken);
        }

        return $this->render(
            'pages/userSettingsEmailChange.html.twig',
            [
                'url'  => $_ENV['FRONTEND_URL'],
                'lang' => $request->getPreferredLanguage() !== null ? $request->getPreferredLanguage() : $translateService->getLocate(),
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
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface $endpointLogger,
        UserDeleteRepository $userDeleteRepository,
        AuthenticationTokenRepository $authenticationTokenRepository,
        UserRepository $userRepository,
        MailerInterface $mailer,
        TranslateService $translateService,
    ): Response {
        $user = $authorizedUserService::getAuthorizedUser();

        $userInDelete = $userDeleteRepository->userInList($user);

        if ($userInDelete) {
            $endpointLogger->error('User in list');
            $translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$translateService->getTranslation('UserDeleteExists')]);
        }

        $user->setActive(false);
        $userRepository->add($user, false);

        $activeAuthenticationToken = $authenticationTokenRepository->getLastActiveUserAuthenticationToken($user);

        if ($activeAuthenticationToken !== null) {
            $activeAuthenticationToken->setDateExpired((new DateTime())->modify('-1 day'));
            $authenticationTokenRepository->add($activeAuthenticationToken, false);
        }

        $userDelete = new UserDelete($user);

        $userDeleteRepository->add($userDelete);

        if ($_ENV['APP_ENV'] !== 'test') {
            $email = (new TemplatedEmail())
                ->from($_ENV['INSTITUTION_EMAIL'])
                ->to($user->getUserInformation()->getEmail())
                ->subject($translateService->getTranslation('RequestDeleteAccountSubject'))
                ->htmlTemplate('emails/userDeleteProcessing.html.twig')
                ->context([
                    'userName' => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                    'lang'     => $request->getPreferredLanguage() !== null ? $request->getPreferredLanguage() : $translateService->getLocate(),
                ]);
            $mailer->send($email);
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
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface $endpointLogger,
        UserEditRepository $editRepository,
        TranslateService $translateService,
        MailerInterface $mailer,
    ): Response {
        $user = $authorizedUserService::getAuthorizedUser();

        $userEdit = $editRepository->checkIfUserCanChange($user, UserEditType::USER_DATA);

        if ($userEdit !== null) {
            $endpointLogger->error('User changed password');
            $translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$translateService->getTranslation('UserChangedUserData')]);
        }

        $newEditedUser = new UserEdit($user, false, UserEditType::USER_DATA);
        $newEditedUser
            ->setCode(new UserEditConfirmGenerator())
            ->setEditableDate((new DateTime())->modify('+15 minutes'));

        $editRepository->add($newEditedUser);

        if ($_ENV['APP_ENV'] !== 'test') {
            $email = (new TemplatedEmail())
                ->from($_ENV['INSTITUTION_EMAIL'])
                ->to($user->getUserInformation()->getEmail())
                ->subject($translateService->getTranslation('ChangeUserDataSubject'))
                ->htmlTemplate('emails/userSettingsChangeCode.html.twig')
                ->context([
                    'userName' => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                    'code'     => $newEditedUser->getCode(),
                    'lang'     => $request->getPreferredLanguage() !== null ? $request->getPreferredLanguage() : $translateService->getLocate(),
                ]);
            $mailer->send($email);
        }

        return ResponseTool::getResponse(new UserChangeCodeSuccessModel($newEditedUser->getCode()), 201);
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
        RequestServiceInterface $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface $endpointLogger,
        UserInformationRepository $userInformationRepository,
        UserEditRepository $editRepository,
        TranslateService $translateService,
    ): Response {
        $userSettingsChangeQuery = $requestService->getRequestBodyContent($request, UserSettingsChangeQuery::class);

        if ($userSettingsChangeQuery instanceof UserSettingsChangeQuery) {
            $user = $authorizedUserService::getAuthorizedUser();

            $userEdit = $editRepository->checkIfUserCanChangeWithCode($user, UserEditType::USER_DATA, $userSettingsChangeQuery->getCode());

            if ($userEdit === null) {
                $endpointLogger->error('User changed password');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('IncorrectCodeOrUserDataWasChanged')]);
            }

            $userEdit->setEdited(true);

            $userInformation = $user->getUserInformation();

            $userInformation
                ->setFirstname($userSettingsChangeQuery->getFirstName())
                ->setLastname($userSettingsChangeQuery->getLastName());

            if ($userInformation->getPhoneNumber() !== $userSettingsChangeQuery->getPhoneNumber()) {
                $existingPhone = $userInformationRepository->findOneBy([
                    'phoneNumber' => $userSettingsChangeQuery->getPhoneNumber(),
                ]);

                if ($existingPhone !== null) {
                    $endpointLogger->error('Phone number already exists');
                    $translateService->setPreferredLanguage($request);
                    throw new DataNotFoundException([$translateService->getTranslation('PhoneNumberExists')]);
                }

                $userInformation->setPhoneNumber($userSettingsChangeQuery->getPhoneNumber());
            }

            $editRepository->add($userEdit, false);
            $userInformationRepository->add($userInformation);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
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
    public function userSettingsGet(
        AuthorizedUserServiceInterface $authorizedUserService,
    ): Response {
        $user = $authorizedUserService::getAuthorizedUser();

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
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        MailerInterface $mailer,
        UserInformationRepository $userInformationRepository,
        UserRepository $userRepository,
        UserEditRepository $editRepository,
        TranslateService $translateService,
    ): Response {
        $userResetPasswordQuery = $requestService->getRequestBodyContent($request, UserResetPasswordQuery::class);

        if ($userResetPasswordQuery instanceof UserResetPasswordQuery) {
            $userInformation = $userInformationRepository->findOneBy([
                'email' => $userResetPasswordQuery->getEmail(),
            ]);

            if ($userInformation === null) {
                $endpointLogger->error('User dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('EmailDontExists')]);
            }

            $user = $userInformation
                ->getUser()
                ->setEdited(true)
                ->setEditableDate(new DateTime());

            $editRepository->changeResetPasswordEdits($user);

            $newEditedUser = new UserEdit($user, false, UserEditType::PASSWORD_RESET);
            $newEditedUser->setEditableDate((new DateTime())->modify('+10 hour'));

            $editRepository->add($newEditedUser);

            $userRepository->add($user);

            if ($_ENV['APP_ENV'] !== 'test') {
                $email = (new TemplatedEmail())
                    ->from($_ENV['INSTITUTION_EMAIL'])
                    ->to($user->getUserInformation()->getEmail())
                    ->subject($translateService->getTranslation('PasswordResetSubject'))
                    ->htmlTemplate('emails/userSettingsResetPassword.html.twig')
                    ->context([
                        'userName' => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                        'id'       => $user->getId()->__toString(),
                        'url'      => $_ENV['FRONTEND_URL'],
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
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        UserRepository $userRepository,
        UserPasswordRepository $userPasswordRepository,
        UserEditRepository $editRepository,
        TranslateService $translateService,
    ): Response {
        $userResetPasswordConfirmQuery = $requestService->getRequestBodyContent($request, UserResetPasswordConfirmQuery::class);

        if ($userResetPasswordConfirmQuery instanceof UserResetPasswordConfirmQuery) {
            $user = $userRepository->find($userResetPasswordConfirmQuery->getUserId());

            if ($user === null) {
                $endpointLogger->error('User dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('EmailDontExists')]);
            }

            $userEdit = $editRepository->checkIfUserCanChange($user, UserEditType::PASSWORD_RESET);

            if ($userEdit === null) {
                $endpointLogger->error('User changed password');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserChangedPassword')]);
            }

            $userEdit->setEdited(true);

            $editRepository->add($userEdit);

            $userRepository->add($user);

            $password = $userPasswordRepository->findOneBy([
                'user' => $user->getId(),
            ]);

            $passwordGenerator = new PasswordHashGenerator($userResetPasswordConfirmQuery->getPassword());

            $password->setPassword($passwordGenerator);

            $userPasswordRepository->add($password);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
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
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface $endpointLogger,
        TranslateService $translateService,
        UserParentalControlCodeRepository $controlCodeRepository,
        UserRepository $userRepository,
    ): Response {
        $user = $authorizedUserService::getAuthorizedUser();

        $lastWeakAttempts = $controlCodeRepository->getUserParentalControlCodeFromLastWeekByUser($user);

        if ($lastWeakAttempts >= 3) {
            $endpointLogger->error('To many attempts to get UserParentalControlCode sms code');
            $translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$translateService->getTranslation('UserParentalControlCodeToManyAttempts')]);
        }

        $controlCodeRepository->setCodesToNotActive($user);

        $newGenerator = new UserParentalControlCodeGenerator();

        $newUserParentalControlCode = new UserParentalControlCode($user, $newGenerator);

        $controlCodeRepository->add($newUserParentalControlCode, false);

        $smsTool = new SmsTool();

        try {
            $status = $smsTool->sendSms($user->getUserInformation()->getPhoneNumber(), $translateService->getTranslation('SmsCodeContent') . ': ' . $newUserParentalControlCode->getCode() . ' ');
        } catch (Throwable $e) {
            $endpointLogger->error($e->getMessage());
            $translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$translateService->getTranslation('SmsCodeError')]);
        }

        if (!$status) {
            $endpointLogger->error("Can't send sms");
            $translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$translateService->getTranslation('SmsCodeError')]);
        }

        $userRepository->add($user);

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
        RequestServiceInterface $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface $endpointLogger,
        UserInformationRepository $userInformationRepository,
        TranslateService $translateService,
        UserParentalControlCodeRepository $controlCodeRepository,
        MailerInterface $mailer,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $userParentControlPatchQuery = $requestService->getRequestBodyContent($request, UserParentControlPatchQuery::class);

        if ($userParentControlPatchQuery instanceof UserParentControlPatchQuery) {
            $user = $authorizedUserService::getAuthorizedUser();

            $controlCode = $controlCodeRepository->findOneBy([
                'code'   => $userParentControlPatchQuery->getSmsCode(),
                'active' => true,
                'user'   => $user->getId(),
            ]);

            if ($controlCode === null) {
                $endpointLogger->error('UserParentalControlCode dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserParentalControlCodeDontExists')]);
            }

            $additionalData = $userParentControlPatchQuery->getAdditionalData();
            $userInformation = $user->getUserInformation();

            $birthday = $additionalData['birthday'] ?? null;

            if ($birthday !== null) {
                $userInformation->setBirthday($birthday);
            } else {
                $userInformation->setBirthday(null);
            }

            $userInformationRepository->add($userInformation);

            $controlCode->setActive(false);
            $controlCodeRepository->add($controlCode);

            if ($_ENV['APP_ENV'] !== 'test') {
                $email = (new TemplatedEmail())
                    ->from($_ENV['INSTITUTION_EMAIL'])
                    ->to($user->getUserInformation()->getEmail())
                    ->subject($translateService->getTranslation('ParentControlChangedSubject'))
                    ->htmlTemplate('emails/userParentControlChanged.html.twig')
                    ->context([
                        'name' => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                        'change'   => $birthday !== null,
                        'lang'     => $request->getPreferredLanguage() !== null ? $request->getPreferredLanguage() : $translateService->getLocate(),
                    ]);
                $mailer->send($email);
            }

            $stockCache->invalidateTags([
                UserStockCacheTags::USER_AUDIOBOOKS->value,
                UserStockCacheTags::USER_PROPOSED_AUDIOBOOKS->value,
                UserStockCacheTags::USER_CATEGORIES_TREE->value,
            ]);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }
}
