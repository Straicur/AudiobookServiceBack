<?php

declare(strict_types=1);

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Entity\AuthenticationToken;
use App\Entity\UserBanHistory;
use App\Enums\BanPeriodRage;
use App\Enums\UserBanType;
use App\Enums\UserLogin;
use App\Enums\UserRolesNames;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Exception\PermissionException;
use App\Model\Common\AuthorizationRoleModel;
use App\Model\Common\AuthorizationRolesModel;
use App\Model\Common\AuthorizationSuccessModel;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Query\Common\AuthorizeQuery;
use App\Repository\AuthenticationTokenRepository;
use App\Repository\UserBanHistoryRepository;
use App\Repository\UserInformationRepository;
use App\Repository\UserPasswordRepository;
use App\Repository\UserRepository;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Service\TranslateService;
use App\Tool\ResponseTool;
use App\ValueGenerator\AuthTokenGenerator;
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
#[OA\Tag(name: 'Authorize')]
class AuthorizationController extends AbstractController
{
    #[Route('/api/authorize', name: 'apiAuthorize', methods: ['POST'])]
    #[OA\Post(
        description: 'Method used to authorize user credentials. Return authorized token',
        security   : [],
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AuthorizeQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: AuthorizationSuccessModel::class),
            ),
        ]
    )]
    public function login(
        Request $request,
        RequestServiceInterface $requestServiceInterface,
        LoggerInterface $usersLogger,
        LoggerInterface $endpointLogger,
        UserInformationRepository $userInformationRepository,
        UserPasswordRepository $userPasswordRepository,
        AuthenticationTokenRepository $authenticationTokenRepository,
        TranslateService $translateService,
        UserBanHistoryRepository $banHistoryRepository,
        UserRepository $userRepository,
        MailerInterface $mailer,
    ): Response {
        $authenticationQuery = $requestServiceInterface->getRequestBodyContent($request, AuthorizeQuery::class);

        if ($authenticationQuery instanceof AuthorizeQuery) {
            $passwordHashGenerator = new PasswordHashGenerator($authenticationQuery->getPassword());

            $userInformationEntity = $userInformationRepository->findOneBy([
                'email' => $authenticationQuery->getEmail(),
            ]);

            if ($userInformationEntity === null) {
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('EmailDontExists')]);
            }

            $user = $userInformationEntity->getUser();

            if ($user->isBanned()) {
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserBanned')]);
            }

            if (!$user->isActive()) {
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('ActivateAccount')]);
            }

            $roles = $user->getRoles();
            $isUser = false;

            foreach ($roles as $role) {
                if ($role->getName() !== UserRolesNames::GUEST->value) {
                    $isUser = true;
                }
            }

            if (!$isUser) {
                throw new PermissionException();
            }

            $passwordEntity = $userPasswordRepository->findOneBy([
                'user' => $user->getId(),
                'password' => $passwordHashGenerator->generate(),
            ]);

            if ($passwordEntity === null) {
                $translateService->setPreferredLanguage($request);

                $loginAttempts = $userInformationEntity->getLoginAttempts();

                if ($loginAttempts < UserLogin::MAX_LOGIN_ATTEMPTS->value) {
                    $userInformationEntity->setLoginAttempts($loginAttempts + 1);
                    $userInformationRepository->add($userInformationEntity);
                } else {
                    $userInformationEntity->setLoginAttempts(0);
                    $userInformationRepository->add($userInformationEntity);

                    $banPeriod = (new DateTime())->modify(BanPeriodRage::HOUR_BAN->value);

                    $banHistory = new UserBanHistory($user, new DateTime(), $banPeriod, UserBanType::MAX_LOGINS_BREAK);
                    $banHistoryRepository->add($banHistory);

                    $user->setBanned(true)
                        ->setBannedTo($banPeriod);

                    $userRepository->add($user);

                    $email = (new TemplatedEmail())
                        ->from($_ENV['INSTITUTION_EMAIL'])
                        ->to($userInformationEntity->getEmail())
                        ->subject($translateService->getTranslation('MaxLoginsMailAttempts'))
                        ->htmlTemplate('emails/userBanTooManyLoginsAttempts.html.twig')
                        ->context([
                            'userName'  => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                            'banPeriod' => BanPeriodRage::HOUR_BAN->value,
                            'lang'      => $request->getPreferredLanguage() !== null ? $request->getPreferredLanguage() : $translateService->getLocate(),
                        ]);
                    $mailer->send($email);

                    throw new DataNotFoundException([$translateService->getTranslation('BannedForBreakMaxLogins')]);
                }

                throw new DataNotFoundException([$translateService->getTranslation('NotActivePassword')]);
            }

            $authenticationToken = $authenticationTokenRepository->getLastActiveUserAuthenticationToken($user);

            if ($authenticationToken === null) {
                $authTokenGenerator = new AuthTokenGenerator($user);
                $authenticationToken = new AuthenticationToken($user, $authTokenGenerator);
                $authenticationTokenRepository->add($authenticationToken);
            }

            $usersLogger->info('LOGIN', [$user->getId()->__toString()]);

            $rolesModel = new AuthorizationRolesModel();

            $isAdmin = false;

            foreach ($roles as $role) {
                $rolesModel->addAuthorizationRoleModel(new AuthorizationRoleModel($role->getName()));

                if ($role->getName() === UserRolesNames::ADMINISTRATOR->value || $role->getName() === UserRolesNames::RECRUITER->value) {
                    $isAdmin = true;
                }
            }

            $userInformationEntity->setLoginAttempts(0);
            $userInformationRepository->add($userInformationEntity);

            $responseModel = new AuthorizationSuccessModel($authenticationToken->getToken(), $rolesModel, $isAdmin);

            return ResponseTool::getResponse($responseModel);
        }

        $endpointLogger->error('Invalid given Query');

        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/api/logout', name: 'apiLogout', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::USER, UserRolesNames::RECRUITER])]
    #[OA\Post(
        description: 'Method used to logout user',
        requestBody: new OA\RequestBody(),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
            ),
        ]
    )]
    public function logout(
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface $usersLogger,
    ): Response {
        $authorizedUserService::unAuthorizeUser();
        $user = $authorizedUserService::getAuthorizedUser();

        $usersLogger->info('LOGOUT', [$user->getId()->__toString()]);

        return ResponseTool::getResponse();
    }

    #[Route('/api/authorize/check', name: 'apiAuthorizeCheck', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::USER, UserRolesNames::RECRUITER])]
    #[OA\Post(
        description: 'Method is checking if given token is authorized',
        security   : [],
        requestBody: new OA\RequestBody(),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
            ),
        ]
    )]
    public function authorizeCheck(): Response
    {
        return ResponseTool::getResponse();
    }
}
