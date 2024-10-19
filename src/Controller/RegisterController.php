<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\RegisterCode;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Query\Common\RegisterConfirmSendQuery;
use App\Query\Common\RegisterQuery;
use App\Repository\RegisterCodeRepository;
use App\Repository\RoleRepository;
use App\Repository\UserInformationRepository;
use App\Repository\UserRepository;
use App\Service\RequestServiceInterface;
use App\Service\TranslateService;
use App\Service\User\UserRegisterService;
use App\Tool\ResponseTool;
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
        LoggerInterface $endpointLogger,
        LoggerInterface $usersLogger,
        TranslateService $translateService,
        UserRegisterService $registerService,
    ): Response {
        $registerQuery = $requestServiceInterface->getRequestBodyContent($request, RegisterQuery::class);

        if ($registerQuery instanceof RegisterQuery) {
            $registerService->checkExistingUsers($registerQuery, $request);
            $registerService->checkInstitutionLimits($request);

            $newUser = $registerService->createUser($registerQuery);

            $registerCode = $registerService->getRegisterCode($newUser);
            $registerService->sendMail($newUser, $registerCode, $request);

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
