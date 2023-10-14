<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Exception\DataNotFoundException;
use App\Model\DataNotFoundModel;
use App\Model\JsonDataInvalidModel;
use App\Model\NotAuthorizeModel;
use App\Model\PermissionNotGrantedModel;
use App\Service\TranslateService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Tool\ResponseTool;

/**
 * ReportController
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
#[OA\Tag(name: "Report")]
class ReportController extends AbstractController
{

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
     */
    #[Route("/api/report", name: "apiReport", methods: ["GET"])]
    #[OA\Get(
        description: "Method used to report for not loged users",
        security: [],
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            ),
        ]
    )]
    public function report(
        Request                   $request,
        LoggerInterface           $usersLogger,
        LoggerInterface           $endpointLogger,
        TranslateService          $translateService
    ): Response
    {
        return ResponseTool::getResponse();
    }
    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @return Response
     * @throws InvalidJsonDataException
     */
    #[Route("/api/report/user", name: "apiReportUser", methods: ["PUT"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Post(
        description: "Endpoint is used for users to reporting",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                // ref: new Model(type: UserAudiobooksQuery::class),
                // type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                // content: new Model(type: UserAudiobooksSuccessModel::class)
            )
        ]
    )]
    public function apiReportUser(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        TranslateService               $translateService
    ): Response
    {
        return ResponseTool::getResponse();
    }
    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @return Response
     * @throws InvalidJsonDataException
     */
    #[Route("/api/report/admin/accept", name: "apiReportAdminAccept", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is used to appect report",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                // ref: new Model(type: UserAudiobooksQuery::class),
                // type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                // content: new Model(type: UserAudiobooksSuccessModel::class)
            )
        ]
    )]
    public function apiReportAdminAccept(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        TranslateService               $translateService
    ): Response
    {
        return ResponseTool::getResponse();
    }
    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @return Response
     * @throws InvalidJsonDataException
     */
    #[Route("/api/report/admin/reject", name: "apiReportAdminReject", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is used to reject report",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                // ref: new Model(type: UserAudiobooksQuery::class),
                // type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                // content: new Model(type: UserAudiobooksSuccessModel::class)
            )
        ]
    )]
    public function apiReportAdminReject(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        TranslateService               $translateService
    ): Response
    {
        return ResponseTool::getResponse();
    }
     /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @return Response
     * @throws InvalidJsonDataException
     */
    #[Route("/api/report/admin/list", name: "apiReportAdminList", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is used to get report list",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                // ref: new Model(type: UserAudiobooksQuery::class),
                // type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                // content: new Model(type: UserAudiobooksSuccessModel::class)
            )
        ]
    )]
    public function apiReportAdminList(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        TranslateService               $translateService
    ): Response
    {
        return ResponseTool::getResponse();
    }
    
}