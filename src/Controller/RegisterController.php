<?php

declare(strict_types = 1);

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
use App\Service\TranslateServiceInterface;
use App\Service\User\UserRegisterServiceInterface;
use App\Tool\ResponseTool;
use App\ValueGenerator\RegisterCodeGenerator;
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
    public function __construct(
        private readonly RequestServiceInterface $requestServiceInterface,
        private readonly LoggerInterface $endpointLogger,
        private readonly LoggerInterface $usersLogger,
        private readonly TranslateServiceInterface $translateService,
        private readonly UserRegisterServiceInterface $registerService,
        private readonly RegisterCodeRepository $registerCodeRepository,
        private readonly RoleRepository $roleRepository,
        private readonly UserRepository $userRepository,
        private readonly UserInformationRepository $userInformationRepository,
        private readonly MailerInterface $mailer,
        #[Autowire(env: 'INSTITUTION_EMAIL')] private readonly string $institutionEmail,
        #[Autowire(env: 'FRONTEND_URL')] private readonly string $frontendUrl,
        #[Autowire(env: 'BACKEND_URL')] private readonly string $backendUrl,
    ) {}

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
    ): Response {
        $registerQuery = $this->requestServiceInterface->getRequestBodyContent($request, RegisterQuery::class);

        if ($registerQuery instanceof RegisterQuery) {
            $this->registerService->checkExistingUsers($registerQuery, $request);
            $this->registerService->checkInstitutionLimits($request);

            $newUser = $this->registerService->createUser($registerQuery);

            $registerCode = $this->registerService->getRegisterCode($newUser);

            /*
             * Now user has to click on a link in mail to activate his account
             */
            $this->registerService->sendMail($newUser, $registerCode, $request);

            $this->usersLogger->info('user.' . $newUser->getUserInformation()->getEmail() . 'registered');

            return ResponseTool::getResponse(httpCode: Response::HTTP_CREATED);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
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
    ): Response {
        $userEmail = $request->get('email');
        $code = $request->get('code');

        $userInformation = $this->userInformationRepository->findOneBy([
            'email' => $userEmail,
        ]);

        if (null === $userInformation) {
            $this->endpointLogger->error('Invalid Credentials');
            $this->translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$this->translateService->getTranslation('UserDontExists')]);
        }

        $user = $userInformation->getUser();
        $registerCodeGenerator = new RegisterCodeGenerator($code);

        $registerCode = $this->registerCodeRepository->findOneBy([
            'code' => $registerCodeGenerator->generate(),
        ]);

        if (null === $registerCode || !$registerCode->getActive() || $registerCode->getDateAccept() !== null || $registerCode->getUser() !== $user) {
            $this->endpointLogger->error('Invalid Credentials');
            $this->translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$this->translateService->getTranslation('WrongCode')]);
        }

        $registerCode->setActive(false);
        $registerCode->setDateAccept(new DateTime());

        $this->registerCodeRepository->add($registerCode);

        $userRole = $this->roleRepository->findOneBy([
            'name' => 'User',
        ]);

        $user->addRole($userRole);
        $user->setActive(true);

        $this->userRepository->add($user);

        $this->usersLogger->info('user.' . $user->getUserInformation()->getEmail() . 'successfully registered and confirmed');

        return $this->render(
            'pages/registered.html.twig',
            [
                'url'  => $this->frontendUrl,
                'lang' => $request->getPreferredLanguage() ?? $this->translateService->getLocate(),
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
    ): Response {
        $registerConfirmSendQuery = $this->requestServiceInterface->getRequestBodyContent($request, RegisterConfirmSendQuery::class);

        if ($registerConfirmSendQuery instanceof RegisterConfirmSendQuery) {
            $userInfo = $this->userInformationRepository->findOneBy([
                'email' => $registerConfirmSendQuery->getEmail(),
            ]);

            if (null === $userInfo) {
                $this->endpointLogger->error('Invalid Credentials');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('UserDontExists')]);
            }

            $user = $userInfo->getUser();

            if ($user->isActive() || $user->isBanned()) {
                $this->endpointLogger->error('Invalid Credentials');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('ActiveOrBanned')]);
            }

            $this->registerCodeRepository->setCodesToNotActive($user);

            $registerCodeGenerator = new RegisterCodeGenerator();

            $registerCode = new RegisterCode($registerCodeGenerator, $user);

            $this->registerCodeRepository->add($registerCode);

            if ('test' !== $_ENV['APP_ENV']) {
                $email = new TemplatedEmail()
                    ->from($this->institutionEmail)
                    ->to($user->getUserInformation()->getEmail())
                    ->subject($this->translateService->getTranslation('AccountActivationCodeSubject'))
                    ->htmlTemplate('emails/register.html.twig')
                    ->context([
                        'userName'  => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                        'code'      => $registerCodeGenerator->getBeforeGenerate(),
                        'userEmail' => $user->getUserInformation()->getEmail(),
                        'url'       => $this->backendUrl,
                        'lang'      => $request->getPreferredLanguage() ?? $this->translateService->getLocate(),
                    ]);
                $this->mailer->send($email);
            }

            $this->usersLogger->info('user.' . $user->getUserInformation()->getEmail() . 'got new confim email');

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }
}
