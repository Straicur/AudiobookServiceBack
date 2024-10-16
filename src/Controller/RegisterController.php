<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\MyList;
use App\Entity\ProposedAudiobooks;
use App\Entity\RegisterCode;
use App\Entity\User;
use App\Entity\UserInformation;
use App\Entity\UserPassword;
use App\Entity\UserSettings;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Query\Common\RegisterConfirmSendQuery;
use App\Query\Common\RegisterQuery;
use App\Repository\InstitutionRepository;
use App\Repository\MyListRepository;
use App\Repository\ProposedAudiobooksRepository;
use App\Repository\RegisterCodeRepository;
use App\Repository\RoleRepository;
use App\Repository\UserInformationRepository;
use App\Repository\UserPasswordRepository;
use App\Repository\UserRepository;
use App\Repository\UserSettingsRepository;
use App\Service\RequestServiceInterface;
use App\Service\TranslateService;
use App\Tool\ResponseTool;
use App\ValueGenerator\PasswordHashGenerator;
use App\ValueGenerator\RegisterCodeGenerator;
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
#[OA\Tag(name: 'Register')]
class RegisterController extends AbstractController
{
    #[Route('/api/register', name: 'apiRegister', methods: ['PUT'])]
    #[OA\Put(
        description: 'Method used to register user',
        security   : [],
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: RegisterQuery::class),
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
    public function register(
        Request $request,
        RequestServiceInterface $requestServiceInterface,
        UserInformationRepository $userInformationRepository,
        UserRepository $userRepository,
        LoggerInterface $endpointLogger,
        LoggerInterface $usersLogger,
        RegisterCodeRepository $registerCodeRepository,
        MailerInterface $mailer,
        RoleRepository $roleRepository,
        MyListRepository $myListRepository,
        ProposedAudiobooksRepository $proposedAudiobooksRepository,
        InstitutionRepository $institutionRepository,
        UserPasswordRepository $userPasswordRepository,
        TranslateService $translateService,
        UserSettingsRepository $userSettingsRepository,
    ): Response {
        $registerQuery = $requestServiceInterface->getRequestBodyContent($request, RegisterQuery::class);

        if ($registerQuery instanceof RegisterQuery) {
            $existingEmail = $userInformationRepository->findOneBy([
                'email' => $registerQuery->getEmail(),
            ]);

            if ($existingEmail !== null) {
                $endpointLogger->error('Email already exists');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('EmailExists')]);
            }

            $existingPhone = $userInformationRepository->findOneBy([
                'phoneNumber' => $registerQuery->getPhoneNumber(),
            ]);

            if ($existingPhone !== null) {
                $endpointLogger->error('Phone number already exists');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('PhoneNumberExists')]);
            }

            $institution = $institutionRepository->findOneBy([
                'name' => $_ENV['INSTITUTION_NAME'],
            ]);

            $guest = $roleRepository->findOneBy([
                'name' => 'Guest',
            ]);

            if ($institution->getMaxUsers() < count($userRepository->getUsersByRole($guest))) {
                $endpointLogger->error('Too much users');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('ToMuchUsers')]);
            }

            $newUser = new User();

            $userRepository->add($newUser, false);

            $additionalData = $registerQuery->getAdditionalData();

            $newUserInformation = new UserInformation(
                $newUser,
                $registerQuery->getEmail(),
                $registerQuery->getPhoneNumber(),
                $registerQuery->getFirstname(),
                $registerQuery->getLastname(),
            );

            $birthday = $additionalData['birthday'] ?? null;

            if ($birthday !== null) {
                $newUserInformation->setBirthday($birthday);
            }

            $newUser->setUserInformation($newUserInformation);

            $userSettingsRepository->add(new UserSettings($newUser));

            $userMyList = new MyList($newUser);

            $myListRepository->add($userMyList);

            $userProposedAudiobooks = new ProposedAudiobooks($newUser);

            $proposedAudiobooksRepository->add($userProposedAudiobooks);

            $userRole = $roleRepository->findOneBy([
                'name' => 'Guest',
            ]);

            $newUser->addRole($userRole);

            $passwordGenerator = new PasswordHashGenerator($registerQuery->getPassword());

            $userPasswordEntity = new UserPassword($newUser, $passwordGenerator);

            $userPasswordRepository->add($userPasswordEntity);

            $registerCodeGenerator = new RegisterCodeGenerator();

            $registerCode = new RegisterCode($registerCodeGenerator, $newUser);

            $registerCodeRepository->add($registerCode);

            if ($_ENV['APP_ENV'] !== 'test') {
                $email = (new TemplatedEmail())
                    ->from($_ENV['INSTITUTION_EMAIL'])
                    ->to($newUser->getUserInformation()->getEmail())
                    ->subject($translateService->getTranslation('AccountActivationCodeSubject'))
                    ->htmlTemplate('emails/register.html.twig')
                    ->context([
                        'userName'  => $newUser->getUserInformation()->getFirstname() . ' ' . $newUser->getUserInformation()->getLastname(),
                        'code'      => $registerCodeGenerator->getBeforeGenerate(),
                        'userEmail' => $newUser->getUserInformation()->getEmail(),
                        'url'       => $_ENV['BACKEND_URL'],
                        'lang'      => $request->getPreferredLanguage() !== null ? $request->getPreferredLanguage() : $translateService->getLocate(),
                    ]);
                $mailer->send($email);
            }

            $usersLogger->info('user.' . $newUser->getUserInformation()->getEmail() . 'registered');
            return ResponseTool::getResponse(httpCode: 201);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/api/register/{email}/{code}', name: 'apiRegisterConfirm', methods: ['GET'])]
    #[OA\Get(
        description: 'Method used to confirm user registration',
        security   : [],
        requestBody: new OA\RequestBody(),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
            ),
        ]
    )]
    public function registerConfirm(
        Request $request,
        LoggerInterface $usersLogger,
        LoggerInterface $endpointLogger,
        RegisterCodeRepository $registerCodeRepository,
        RoleRepository $roleRepository,
        UserRepository $userRepository,
        UserInformationRepository $userInformationRepository,
        TranslateService $translateService,
    ): Response {
        $userEmail = $request->get('email');
        $code = $request->get('code');

        $userInformation = $userInformationRepository->findOneBy([
            'email' => $userEmail,
        ]);

        if ($userInformation === null) {
            $endpointLogger->error('Invalid Credentials');
            $translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$translateService->getTranslation('UserDontExists')]);
        }

        $user = $userInformation->getUser();
        $registerCodeGenerator = new RegisterCodeGenerator($code);

        $registerCode = $registerCodeRepository->findOneBy([
            'code' => $registerCodeGenerator->generate(),
        ]);

        if ($registerCode === null || !$registerCode->getActive() || $registerCode->getDateAccept() !== null || $registerCode->getUser() !== $user) {
            $endpointLogger->error('Invalid Credentials');
            $translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$translateService->getTranslation('WrongCode')]);
        }

        $registerCode->setActive(false);
        $registerCode->setDateAccept(new DateTime());

        $registerCodeRepository->add($registerCode);

        $userRole = $roleRepository->findOneBy([
            'name' => 'User',
        ]);

        $user->addRole($userRole);
        $user->setActive(true);

        $userRepository->add($user);

        $usersLogger->info('user.' . $user->getUserInformation()->getEmail() . 'successfully registered and confirmed');

        return $this->render(
            'pages/registered.html.twig',
            [
                'url'  => $_ENV['FRONTEND_URL'],
                'lang' => $request->getPreferredLanguage() !== null ? $request->getPreferredLanguage() : $translateService->getLocate(),
            ],
        );
    }

    #[Route('/api/register/code/send', name: 'apiRegisterCodeSend', methods: ['POST'])]
    #[OA\Post(
        description: 'Method used to send registration code again',
        security   : [],
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: RegisterConfirmSendQuery::class),
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
    public function registerCodeSend(
        Request $request,
        RequestServiceInterface $requestServiceInterface,
        LoggerInterface $endpointLogger,
        LoggerInterface $usersLogger,
        MailerInterface $mailer,
        RegisterCodeRepository $registerCodeRepository,
        UserInformationRepository $userInformationRepository,
        TranslateService $translateService,
    ): Response {
        $registerConfirmSendQuery = $requestServiceInterface->getRequestBodyContent($request, RegisterConfirmSendQuery::class);

        if ($registerConfirmSendQuery instanceof RegisterConfirmSendQuery) {
            $userInfo = $userInformationRepository->findOneBy([
                'email' => $registerConfirmSendQuery->getEmail(),
            ]);

            if ($userInfo === null) {
                $endpointLogger->error('Invalid Credentials');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserDontExists')]);
            }

            $user = $userInfo->getUser();

            if ($user->isActive() || $user->isBanned()) {
                $endpointLogger->error('Invalid Credentials');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('ActiveOrBanned')]);
            }

            $registerCodeRepository->setCodesToNotActive($user);

            $registerCodeGenerator = new RegisterCodeGenerator();

            $registerCode = new RegisterCode($registerCodeGenerator, $user);

            $registerCodeRepository->add($registerCode);

            if ($_ENV['APP_ENV'] !== 'test') {
                $email = (new TemplatedEmail())
                    ->from($_ENV['INSTITUTION_EMAIL'])
                    ->to($user->getUserInformation()->getEmail())
                    ->subject($translateService->getTranslation('AccountActivationCodeSubject'))
                    ->htmlTemplate('emails/register.html.twig')
                    ->context([
                        'userName'  => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                        'code'      => $registerCodeGenerator->getBeforeGenerate(),
                        'userEmail' => $user->getUserInformation()->getEmail(),
                        'url'       => $_ENV['BACKEND_URL'],
                        'lang'      => $request->getPreferredLanguage() !== null ? $request->getPreferredLanguage() : $translateService->getLocate(),
                    ]);
                $mailer->send($email);
            }

            $usersLogger->info('user.' . $user->getUserInformation()->getEmail() . 'got new confim email');
            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }
}
