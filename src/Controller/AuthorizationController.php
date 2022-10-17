<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Entity\AuthenticationToken;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\AuthorizationSuccessModel;
use App\Model\DataNotFoundModel;
use App\Model\JsonDataInvalidModel;
use App\Query\AuthorizeQuery;
use App\Repository\AuthenticationTokenRepository;
use App\Repository\PdfDocsRepository;
use App\Repository\UserInformationRepository;
use App\Repository\UserPasswordRepository;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Tool\ResponseTool;
use App\ValueGenerator\AuthTokenGenerator;
use App\ValueGenerator\PasswordHashGenerator;
use Doctrine\ORM\NonUniqueResultException;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * AuthorizationController
 *
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
#[OA\Tag(name: "Authorize")]
class AuthorizationController extends AbstractController
{
    /**
     * @throws InvalidJsonDataException
     * @throws DataNotFoundException
     * @throws TransportExceptionInterface
     *
     */
    #[Route("/api/authorize", name: "apiAuthorize", methods: ["POST"])]
    #[OA\Post(
        description: "Method used to authorize user credentials. Return authorized token",
        security: [],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AuthorizeQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AuthorizationSuccessModel::class)
            ),
        ]
    )]
    public function login(
        Request                       $request,
        RequestServiceInterface       $requestServiceInterface,
        LoggerInterface               $usersLogger,
        UserInformationRepository     $userInformationRepository,
        UserPasswordRepository        $userPasswordRepository,
        AuthenticationTokenRepository $authenticationTokenRepository
    ): ?Response
    {
        $authenticationQuery = $requestServiceInterface->getRequestBodyContent($request, AuthorizeQuery::class);

        $passwordHashGenerator = new PasswordHashGenerator($authenticationQuery->getPassword());

        $userInformationEntity = $userInformationRepository->findOneBy([
            "email" => $authenticationQuery->getEmail()
        ]);

        if ($userInformationEntity == null) {
            throw new DataNotFoundException(["user.credentials"]);
        } else {

            if($userInformationEntity->getUser()->isBanned()){
                 throw new DataNotFoundException(["user.banned"]);
            }

            $passwordEntity = $userPasswordRepository->findOneBy([
                "user" => $userInformationEntity->getUser(),
                "password" => $passwordHashGenerator->generate()
            ]);

            if ($passwordEntity == null) {
                throw new DataNotFoundException(["user.credentials"]);
            } else {
                $authTokenGenerator = new AuthTokenGenerator($userInformationEntity->getUser());

                $authenticationToken = new AuthenticationToken($userInformationEntity->getUser(), $authTokenGenerator);
                $authenticationTokenRepository->add($authenticationToken);

                $usersLogger->info("LOGIN", [$userInformationEntity->getUser()->getId()->__toString()]);

                $responseModel = new AuthorizationSuccessModel($authenticationToken->getToken());
            }
        }

        return ResponseTool::getResponse($responseModel);
    }

    /**
     * @param Request $request
     * @param AuthenticationTokenRepository $authenticationTokenRepository
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $usersLogger
     * @return Response
     * @throws NonUniqueResultException
     */
    #[Route("/api/logout", name: "apiLogout", methods: ["POST"])]
    #[OA\Post(
        description: "Method used to logout user",
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            ),
        ]
    )]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    public function logout(
        Request                        $request,
        AuthenticationTokenRepository  $authenticationTokenRepository,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $usersLogger,
    ): Response
    {
        $user = $authorizedUserService->getAuthorizedUser();

        $authorizationHeaderField = $request->headers->get("authorization");

        $authToken = $authenticationTokenRepository->findActiveToken($authorizationHeaderField);

        $authToken->setDateExpired(new \DateTime('NOW'));
        $authenticationTokenRepository->add($authToken);

        $usersLogger->info("LOGIN", [$user->getId()->__toString()]);

        return ResponseTool::getResponse();
    }
}