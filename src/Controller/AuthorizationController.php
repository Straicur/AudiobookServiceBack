<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Entity\AuthenticationToken;
use App\Exception\DataNotFoundException;
use App\Exception\PermissionException;
use App\Model\AuthorizationRoleModel;
use App\Model\AuthorizationRolesModel;
use App\Model\AuthorizationSuccessModel;
use App\Model\DataNotFoundModel;
use App\Model\JsonDataInvalidModel;
use App\Model\NotAuthorizeModel;
use App\Model\PermissionNotGrantedModel;
use App\Query\AuthorizeQuery;
use App\Repository\AuthenticationTokenRepository;
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
use Symfony\Component\Routing\Annotation\Route;

/**
 * AuthorizationController
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
#[OA\Tag(name: "Authorize")]
class AuthorizationController extends AbstractController
{
    /**
     * @param Request $request
     * @param RequestServiceInterface $requestServiceInterface
     * @param LoggerInterface $usersLogger
     * @param UserInformationRepository $userInformationRepository
     * @param UserPasswordRepository $userPasswordRepository
     * @param AuthenticationTokenRepository $authenticationTokenRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws PermissionException
     * @throws \Exception
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
    ): Response
    {
        $authenticationQuery = $requestServiceInterface->getRequestBodyContent($request, AuthorizeQuery::class);

        $passwordHashGenerator = new PasswordHashGenerator($authenticationQuery->getPassword());

        $userInformationEntity = $userInformationRepository->findOneBy([
            "email" => $authenticationQuery->getEmail()
        ]);

        if ($userInformationEntity == null) {
            throw new DataNotFoundException(["user.credentials"]);
        }

        if ($userInformationEntity->getUser()->isBanned()) {
            throw new DataNotFoundException(["user.banned"]);
        }

        if (!$userInformationEntity->getUser()->isActive()) {
            throw new DataNotFoundException(["user.not.active"]);
        }

        $roles = $userInformationEntity->getUser()->getRoles();
        $isUser = false;

        foreach ($roles as $role) {
            if ($role->getName() == "User" || $role->getName() == "Administrator") {
                $isUser = true;
            }
        }

        if (!$isUser) {
            throw new PermissionException("user.credentials");
        }
        $passwordEntity = $userPasswordRepository->findOneBy([
            "user" => $userInformationEntity->getUser(),
            "password" => $passwordHashGenerator->generate()
        ]);

        if ($passwordEntity == null) {
            throw new DataNotFoundException(["user.credentials"]);
        }

        $authTokenGenerator = new AuthTokenGenerator($userInformationEntity->getUser());

        $authenticationToken = new AuthenticationToken($userInformationEntity->getUser(), $authTokenGenerator);
        $authenticationTokenRepository->add($authenticationToken);

        $usersLogger->info("LOGIN", [$userInformationEntity->getUser()->getId()->__toString()]);

        $rolesModel = new AuthorizationRolesModel();

        foreach ($userInformationEntity->getUser()->getRoles() as $role) {
            $rolesModel->addAuthorizationRoleModel(new AuthorizationRoleModel($role->getName()));
        }

        $responseModel = new AuthorizationSuccessModel($authenticationToken->getToken(), $rolesModel);

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
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator", "User"])]
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

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestServiceInterface
     * @param LoggerInterface $usersLogger
     * @return Response
     */
    #[Route("/api/authorize/check", name: "apiAuthorizeCheck", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator", "User"])]
    #[OA\Post(
        description: "Method is checking if given token is authorized",
        security: [],
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            ),
        ]
    )]
    public function authorizeCheck(
        Request                 $request,
        RequestServiceInterface $requestServiceInterface,
        LoggerInterface         $usersLogger,
    ): Response
    {
        return ResponseTool::getResponse();
    }
}