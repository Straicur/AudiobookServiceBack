<?php

namespace App\Controller;

use App\Entity\MyList;
use App\Entity\ProposedAudiobooks;
use App\Entity\RegisterCode;
use App\Entity\User;
use App\Entity\UserInformation;
use App\Entity\UserPassword;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\AuthorizationSuccessModel;
use App\Model\DataNotFoundModel;
use App\Model\JsonDataInvalidModel;
use App\Query\RegisterConfirmSendQuery;
use App\Query\RegisterQuery;
use App\Repository\InstitutionRepository;
use App\Repository\MyListRepository;
use App\Repository\ProposedAudiobooksRepository;
use App\Repository\RegisterCodeRepository;
use App\Repository\RoleRepository;
use App\Repository\UserInformationRepository;
use App\Repository\UserPasswordRepository;
use App\Repository\UserRepository;
use App\Service\RequestServiceInterface;
use App\Tool\ResponseTool;
use App\ValueGenerator\PasswordHashGenerator;
use App\ValueGenerator\RegisterCodeGenerator;
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
 * RegisterController
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
#[OA\Tag(name: "Register")]
class RegisterController extends AbstractController
{
    /**
     * @param Request $request
     * @param RequestServiceInterface $requestServiceInterface
     * @param UserInformationRepository $userInformationRepository
     * @param UserRepository $userRepository
     * @param LoggerInterface $endpointLogger
     * @param LoggerInterface $usersLogger
     * @param RegisterCodeRepository $registerCodeRepository
     * @param MailerInterface $mailer
     * @param RoleRepository $roleRepository
     * @param MyListRepository $myListRepository
     * @param ProposedAudiobooksRepository $proposedAudiobooksRepository
     * @param InstitutionRepository $institutionRepository
     * @param UserPasswordRepository $userPasswordRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    #[Route("/api/register", name: "apiRegister", methods: ["PUT"])]
    #[OA\Put(
        description: "Method used to register user",
        security: [],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: RegisterQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            ),
        ]
    )]
    public function register(
        Request                      $request,
        RequestServiceInterface      $requestServiceInterface,
        UserInformationRepository    $userInformationRepository,
        UserRepository               $userRepository,
        LoggerInterface              $endpointLogger,
        LoggerInterface              $usersLogger,
        RegisterCodeRepository       $registerCodeRepository,
        MailerInterface              $mailer,
        RoleRepository               $roleRepository,
        MyListRepository             $myListRepository,
        ProposedAudiobooksRepository $proposedAudiobooksRepository,
        InstitutionRepository        $institutionRepository,
        UserPasswordRepository $userPasswordRepository
    ): Response
    {
        $registerQuery = $requestServiceInterface->getRequestBodyContent($request, RegisterQuery::class);

        if ($registerQuery instanceof RegisterQuery) {

            $duplicateUser = $userInformationRepository->findOneBy([
                "email" => $registerQuery->getEmail()
            ]);

            if ($duplicateUser != null) {
                $endpointLogger->error("Email already exists");
                throw new DataNotFoundException(["register.put.invalid.email"]);
            }

            $institution = $institutionRepository->findOneBy([
                "name" => $_ENV["INSTITUTION_NAME"]
            ]);

            $guest = $roleRepository->findOneBy([
                "name" => "Guest"
            ]);

            if ($institution->getMaxUsers() < count($userRepository->getUsersByRole($guest))) {
                $endpointLogger->error("Too much users");
                throw new DataNotFoundException(["register.put.invalid.amount.of.users"]);
            }

            $newUser = new User();

            $newUser->setUserInformation(new UserInformation(
                $newUser,
                $registerQuery->getEmail(),
                $registerQuery->getPhoneNumber(),
                $registerQuery->getFirstname(),
                $registerQuery->getLastname()
            ));

            $userMyList = new MyList($newUser);

            $myListRepository->add($userMyList);

            $userProposedAudiobooks = new ProposedAudiobooks($newUser);

            $proposedAudiobooksRepository->add($userProposedAudiobooks);

            $userRole = $roleRepository->findOneBy([
                "name" => "Guest"
            ]);

            $newUser->addRole($userRole);

            $passwordGenerator = new PasswordHashGenerator($registerQuery->getPassword());

            $userPasswordEntity = new UserPassword($newUser, $passwordGenerator);

            $userPasswordRepository->add($userPasswordEntity);

            $userRepository->add($newUser);

            $registerCodeGenerator = new RegisterCodeGenerator();

            $registerCode = new RegisterCode($registerCodeGenerator, $newUser);

            $registerCodeRepository->add($registerCode);

            if ($_ENV["APP_ENV"] != "test") {
                $email = (new TemplatedEmail())
                    ->from('mosinskidamian12@gmail.com')
                    ->to($newUser->getUserInformation()->getEmail())
                    ->subject('Kod aktywacji konta')
                    ->htmlTemplate('emails/register.html.twig')
                    ->context([
                        "userName" => $newUser->getUserInformation()->getFirstname() . ' ' . $newUser->getUserInformation()->getLastname(),
                        "code" => $registerCodeGenerator->getBeforeGenerate(),
                        "userEmail" => $newUser->getUserInformation()->getEmail(),
                        "url" => "http://127.0.0.1:8000"
                    ]);
                $mailer->send($email);
            }

            $usersLogger->info("user." . $newUser->getUserInformation()->getEmail() . "registered");
            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("register.put.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param LoggerInterface $usersLogger
     * @param LoggerInterface $endpointLogger
     * @param RegisterCodeRepository $registerCodeRepository
     * @param RoleRepository $roleRepository
     * @param UserRepository $userRepository
     * @param UserInformationRepository $userInformationRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws \Exception
     */
    #[Route("/api/register/{email}/{code}", name: "apiRegisterConfirm", methods: ["GET"])]
    #[OA\Patch(
        description: "Method used to confirm user registration",
        security: [],
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AuthorizationSuccessModel::class)
            ),
        ]
    )]
    public function registerConfirm(
        Request                   $request,
        LoggerInterface           $usersLogger,
        LoggerInterface           $endpointLogger,
        RegisterCodeRepository    $registerCodeRepository,
        RoleRepository            $roleRepository,
        UserRepository            $userRepository,
        UserInformationRepository $userInformationRepository,
    ): Response
    {
        $userEmail = $request->get('email');
        $code = $request->get('code');

        $userInformation = $userInformationRepository->findOneBy([
            "email" => $userEmail
        ]);

        if ($userInformation == null) {
            $endpointLogger->error("Invalid Credentials");
            throw new DataNotFoundException(["register.confirm.code.credentials"]);
        }

        $user = $userInformation->getUser();
        $registerCodeGenerator = new RegisterCodeGenerator($code);

        $registerCode = $registerCodeRepository->findOneBy([
            "code" => $registerCodeGenerator->generate()
        ]);

        if ($registerCode == null || !$registerCode->getActive() || $registerCode->getDateAccept() != null || $registerCode->getUser() !== $user) {
            $endpointLogger->error("Invalid Credentials");
            throw new DataNotFoundException(["register.confirm.code.credentials"]);
        }

        $registerCode->setActive(false);
        $registerCode->setDateAccept(new \DateTime('Now'));

        $registerCodeRepository->add($registerCode);

        $userRole = $roleRepository->findOneBy([
            "name" => "User"
        ]);

        $user->addRole($userRole);
        $user->setActive(true);

        $userRepository->add($user);

        $usersLogger->info("user." . $user->getUserInformation()->getEmail() . "successfully registered and confirmed");

        return $this->render(
            'pages/registered.html.twig',
            [
                "url"=>$_ENV["FRONTEND_URL"]
            ]
        );
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestServiceInterface
     * @param LoggerInterface $endpointLogger
     * @param LoggerInterface $usersLogger
     * @param MailerInterface $mailer
     * @param RegisterCodeRepository $registerCodeRepository
     * @param UserInformationRepository $userInformationRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     * @throws TransportExceptionInterface
     */
    #[Route("/api/register/code/send", name: "apiRegisterCodeSend", methods: ["POST"])]
    #[OA\Post(
        description: "Method used to send registration code again",
        security: [],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: RegisterConfirmSendQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            ),
        ]
    )]
    public function registerCodeSend(
        Request                   $request,
        RequestServiceInterface   $requestServiceInterface,
        LoggerInterface           $endpointLogger,
        LoggerInterface           $usersLogger,
        MailerInterface           $mailer,
        RegisterCodeRepository    $registerCodeRepository,
        UserInformationRepository $userInformationRepository
    ): Response
    {
        $registerConfirmSendQuery = $requestServiceInterface->getRequestBodyContent($request, RegisterConfirmSendQuery::class);

        if ($registerConfirmSendQuery instanceof RegisterConfirmSendQuery) {

            $userInfo = $userInformationRepository->findOneBy([
                "email" => $registerConfirmSendQuery->getEmail()
            ]);

            if ($userInfo == null) {
                $endpointLogger->error("Invalid Credentials");
                throw new DataNotFoundException(["register.code.send.user.credentials"]);
            }

            $user = $userInfo->getUser();

            if ($user->isActive() || $user->isBanned()) {
                $endpointLogger->error("Invalid Credentials");
                throw new DataNotFoundException(["register.code.send.user.credentials"]);
            }

            $registerCodeRepository->setCodesToNotActive($user);

            $registerCodeGenerator = new RegisterCodeGenerator();

            $registerCode = new RegisterCode($registerCodeGenerator, $user);

            $registerCodeRepository->add($registerCode);

            if ($_ENV["APP_ENV"] != "test") {
                $email = (new TemplatedEmail())
                    ->from('mosinskidamian12@gmail.com')
                    ->to($user->getUserInformation()->getEmail())
                    ->subject('Kod aktywacji konta')
                    ->htmlTemplate('emails/register.html.twig')
                    ->context([
                        "userName" => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                        "code" => $registerCodeGenerator->getBeforeGenerate(),
                        "userEmail" => $user->getUserInformation()->getEmail(),
                        "url" => "http://127.0.0.1:8000"
                    ]);
                $mailer->send($email);
            }

            $usersLogger->info("user." . $user->getUserInformation()->getEmail() . "got new confim email");
            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("register.code.send.invalid.query");
        }
    }
}