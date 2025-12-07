<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Enums\UserRolesNames;
use App\Exception\InvalidJsonDataException;
use App\Model\Common\AuthorizationRoleModel;
use App\Model\Common\AuthorizationRolesModel;
use App\Model\Common\AuthorizationSuccessModel;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Query\Common\AuthorizeQuery;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Service\TranslateServiceInterface;
use App\Service\User\UserLoginServiceInterface;
use App\Tool\ResponseTool;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function __construct(private readonly RequestServiceInterface $requestServiceInterface, private readonly LoggerInterface $usersLogger, private readonly LoggerInterface $endpointLogger, private readonly UserLoginServiceInterface $loginService, private readonly TranslateServiceInterface $translateService, private readonly AuthorizedUserServiceInterface $authorizedUserService) {}

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
    ): Response {
        $authenticationQuery = $this->requestServiceInterface->getRequestBodyContent($request, AuthorizeQuery::class);

        if ($authenticationQuery instanceof AuthorizeQuery) {
            $userInformation = $this->loginService->getUserInformation($authenticationQuery->getEmail(), $request);
            $user = $this->loginService->getValidUser($userInformation, $request);
            $this->loginService->loginToService($userInformation, $request, $authenticationQuery->getPassword());
            $this->loginService->resetLoginAttempts($userInformation);
            $authenticationToken = $this->loginService->getAuthenticationToken($user);

            $this->usersLogger->info('LOGIN', [$user->getId()->__toString()]);

            $rolesModel = new AuthorizationRolesModel();
            $isAdmin = false;

            foreach ($user->getRoles() as $role) {
                $rolesModel->addAuthorizationRoleModel(new AuthorizationRoleModel($role->getName()));

                if ($role->getName() === UserRolesNames::ADMINISTRATOR->value || $role->getName() === UserRolesNames::RECRUITER->value) {
                    $isAdmin = true;
                }
            }

            $responseModel = new AuthorizationSuccessModel($authenticationToken->getToken(), $rolesModel, $isAdmin);

            return ResponseTool::getResponse($responseModel);
        }

        $this->endpointLogger->error('Invalid given Query');

        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
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
    public function logout(): Response
    {
        $this->authorizedUserService::unAuthorizeUser();
        $user = $this->authorizedUserService::getAuthorizedUser();
        $this->usersLogger->info('LOGOUT', [$user->getId()->__toString()]);

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
