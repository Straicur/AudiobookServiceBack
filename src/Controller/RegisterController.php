<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Entity\AuthenticationToken;
use App\Entity\RegisterCode;
use App\Entity\User;
use App\Entity\UserInformation;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\AuthorizationSuccessModel;
use App\Model\DataNotFoundModel;
use App\Model\JsonDataInvalidModel;
use App\Query\RegisterConfirmSendQuery;
use App\Query\RegisterQuery;
use App\Repository\AuthenticationTokenRepository;
use App\Repository\RegisterCodeRepository;
use App\Repository\RoleRepository;
use App\Repository\UserInformationRepository;
use App\Repository\UserPasswordRepository;
use App\Repository\UserRepository;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Tool\ResponseTool;
use App\ValueGenerator\AuthTokenGenerator;
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
    //Dodający - ma dodać plus daje mu range Guest, po czym wysyłam email z linkiem(generacja jak password że trzymam w hashu)
    // //dodaj mu też listę od razu i jeszcze sprawdzenie czy w instytucji nie ma już odpowiednich danych

    //Odbierający na getcie i on zczytuje(szuka czy jest aktywny i czy nie ma już odpowiedniej rangi),dezaktywuje i ustawia rangę User
    //Wysyłający jeszcze raz, a reszte dezaktywuje
    /**
     * @param Request $request
     * @param RequestServiceInterface $requestServiceInterface
     * @param UserInformationRepository $userInformationRepository
     * @param UserRepository $userRepository
     * @param LoggerInterface $endpointLogger
     * @param RegisterCodeRepository $registerCodeRepository
     * @param MailerInterface $mailer
     * @param RoleRepository $roleRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     * @throws TransportExceptionInterface
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
        Request                   $request,
        RequestServiceInterface   $requestServiceInterface,
        UserInformationRepository $userInformationRepository,
        UserRepository            $userRepository,
        LoggerInterface           $endpointLogger,
        RegisterCodeRepository    $registerCodeRepository,
        MailerInterface           $mailer,
        RoleRepository            $roleRepository
    ): Response
    {
        $registerQuery = $requestServiceInterface->getRequestBodyContent($request, RegisterQuery::class);

        if ($registerQuery instanceof RegisterQuery) {

//            $duplicateUser = $userInformationRepository->findOneBy([
//                "email" => $registerQuery->getEmail()
//            ]);
//
//            if ($duplicateUser != null) {
//                $endpointLogger->error("Email already exists");
//                throw new DataNotFoundException(["register.put.invalid.email"]);
//            }
//
//            $newUser = new User();
//
//            $newUser->setUserInformation(new UserInformation(
//                $newUser,
//                $registerQuery->getEmail(),
//                $registerQuery->getPhoneNumber(),
//                $registerQuery->getFirstname(),
//                $registerQuery->getLastname()
//            ));
//
//            $userRole = $roleRepository->findOneBy([
//                "name" => "Guest"
//            ]);
//
//            $newUser->addRole($userRole);
//            $newUser->setActive(true);
//
//            $userRepository->add($newUser);
//
//            $registerCodeGenerator = new RegisterCodeGenerator();
//
//            $registerCode = new RegisterCode($newUser, $registerCodeGenerator);
//
//            $registerCodeRepository->add($registerCode);
//
//            if ($_ENV["APP_ENV"] != "test") {
//                $email = (new TemplatedEmail())
//                    ->from('mosinskidamian12@gmail.com')
//                    ->to($newUser->getUserInformation()->getEmail())
//                    ->subject('Kod aktywacji konta')
//                    ->htmlTemplate('emails/register.html.twig')
//                    ->context([
//                        "userName" => $newUser->getUserInformation()->getFirstname() . ' ' . $newUser->getUserInformation()->getLastname(),
//                        "code" => $registerCode->getCode()
//                    ]);
//
//                $mailer->send($email);
//            }

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("register.put.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestServiceInterface
     * @param LoggerInterface $usersLogger
     * @param UserPasswordRepository $userPasswordRepository
     * @param AuthenticationTokenRepository $authenticationTokenRepository
     * @param LoggerInterface $endpointLogger
     * @param RegisterCodeRepository $registerCodeRepository
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param RoleRepository $roleRepository
     * @param UserRepository $userRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/register/code", name: "apiRegisterConfirm", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Guest"])]
    #[OA\Patch(
        description: "Method used to confirm user registration",
        security: [],
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
//                content: new Model(type: AuthorizationSuccessModel::class)
            ),
        ]
    )]
    public function registerConfirm(
        Request                        $request,
        RequestServiceInterface        $requestServiceInterface,
        LoggerInterface                $usersLogger,
        UserPasswordRepository         $userPasswordRepository,
        AuthenticationTokenRepository  $authenticationTokenRepository,
        LoggerInterface                $endpointLogger,
        RegisterCodeRepository         $registerCodeRepository,
        AuthorizedUserServiceInterface $authorizedUserService,
        RoleRepository                 $roleRepository,
        UserRepository                 $userRepository
    ): Response
    {
//        $registerConfirmQuery = $requestServiceInterface->getRequestBodyContent($request, RegisterConfirmQuery::class);
//
//        if ($registerConfirmQuery instanceof RegisterConfirmQuery) {
//
//            $user = $authorizedUserService->getAuthorizedUser();
//
//            $registerCode = $registerCodeRepository->findOneBy([
//                "code" => $registerConfirmQuery->getRegisterCode(),
//                "user" => $user->getId()
//            ]);
//
//            if ($registerCode == null || $registerCode->getDateAccept() != null || $registerCode->getUsed()) {
//                $endpointLogger->error("Invalid Credentials");
//                throw new DataNotFoundException(["register.confirm.code.credentials"]);
//            }
//
//            $registerCode->setUsed(true);
//            $registerCode->setDateAccept(new \DateTime('Now'));
//
//            $registerCodeRepository->add($registerCode);
//
//            $userRole = $roleRepository->findOneBy([
//                "name" => "User"
//            ]);
//
//            $user->addRole($userRole);
//            $user->setActive(true);
//
//            $userRepository->add($user);
//
//            $passwordEntity = $userPasswordRepository->findOneBy([
//                "user" => $user,
//            ]);
//
//            if ($passwordEntity == null) {
//                $endpointLogger->error("Invalid Credentials");
//                throw new DataNotFoundException(["register.confirm.password.credentials"]);
//            }
//
//            $authTokenGenerator = new AuthTokenGenerator($user);
//
//            $authenticationToken = new AuthenticationToken($user, $authTokenGenerator);
//            $authenticationTokenRepository->add($authenticationToken);
//
//            $usersLogger->info("LOGIN", [$user->getId()->__toString()]);
//
//            $responseModel = new AuthorizationSuccessModel($authenticationToken->getToken());
//
//            return ResponseTool::getResponse($responseModel);
//        } else {
//            $endpointLogger->error("Invalid given Query");
//            throw new InvalidJsonDataException("register.confirm.patch.invalid.query");
//        }
        return ResponseTool::getResponse();
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestServiceInterface
     * @param LoggerInterface $endpointLogger
     * @param MailerInterface $mailer
     * @param RegisterCodeRepository $registerCodeRepository
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     * @throws TransportExceptionInterface
     */
    #[Route("/api/register/code/send", name: "apiRegisterCodeSend", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Guest"])]
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
        Request                        $request,
        RequestServiceInterface        $requestServiceInterface,
        LoggerInterface                $endpointLogger,
        MailerInterface                $mailer,
        RegisterCodeRepository         $registerCodeRepository,
        AuthorizedUserServiceInterface $authorizedUserService,
    ): Response
    {
        $registerConfirmSendQuery = $requestServiceInterface->getRequestBodyContent($request, RegisterConfirmSendQuery::class);

        if ($registerConfirmSendQuery instanceof RegisterConfirmSendQuery) {

//            $user = $authorizedUserService->getAuthorizedUser();
//
//            if ($user->getUserInformation()->getEmail() != $registerConfirmSendQuery->getEmail()) {
//                $endpointLogger->error("Invalid Credentials");
//                throw new DataNotFoundException(["register.code.send.user.credentials"]);
//            }
//
//            $registerCode = $registerCodeRepository->getUsedCode($user);
//
//            if ($registerCode != null) {
//                $endpointLogger->error("Invalid Credentials");
//                throw new DataNotFoundException(["register.code.send.invalid.credentials"]);
//            }
//
//            $registerCodeGenerator = new RegisterCodeGenerator();
//
//            $registerCode = new RegisterCode($user, $registerCodeGenerator);
//
//            $registerCodeRepository->add($registerCode);
//
//            if ($_ENV["APP_ENV"] != "test") {
//                $email = (new TemplatedEmail())
//                    ->from('mosinskidamian12@gmail.com')
//                    ->to($user->getUserInformation()->getEmail())
//                    ->subject('Kod aktywacji konta')
//                    ->htmlTemplate('emails/register.html.twig')
//                    ->context([
//                        "userName" => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
//                        "code" => $registerCode->getCode()
//                    ]);
//
//                $mailer->send($email);
//            }

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("register.code.send.invalid.query");
        }
    }
}