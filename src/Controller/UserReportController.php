<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Exception\InvalidJsonDataException;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Query\User\NotAuthorizedUserReportQuery;
use App\Query\User\UserReportQuery;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Service\TranslateService;
use App\Tool\ResponseTool;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * UserController
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
#[OA\Tag(name: "User")]
class UserReportController extends AbstractController
{
    /**
     * @param Request $request
     * @param LoggerInterface $usersLogger
     * @param LoggerInterface $endpointLogger
     * @param TranslateService $translateService
     * @return Response
     */
    #[Route("/api/report", name: "apiReport", methods: ["PUT"])]
    #[OA\Put(
        description: "Method used to report for not loged users",
        security: [],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: NotAuthorizedUserReportQuery::class),
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
    public function report(
        Request          $request,
        LoggerInterface  $usersLogger,
        LoggerInterface  $endpointLogger,
        TranslateService $translateService
    ): Response
    {
        //TODO tu dostaje dodatkowo ip i sprawdzam czy dziś już wysłał minimum 3 zgłoszenia
        // Jeśli tak to nie dodaje nic
        return ResponseTool::getResponse();
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @return Response
     * @throws InvalidJsonDataException
     */
    #[Route("/api/report/user", name: "apiUserReport", methods: ["PUT"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Post(
        description: "Endpoint is used for users to report bad behavior",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserReportQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
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
        //TODO tu muszę też sprawdzić czy nie robi już za dużo tego samego typu zgłoszeń(max 2)
        return ResponseTool::getResponse(httpCode: 201);
    }
}
