<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Entity\Report;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Query\User\UserNotAuthorizedUserReportQuery;
use App\Query\User\UserReportQuery;
use App\Repository\ReportRepository;
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
     * @param RequestServiceInterface $requestService
     * @param LoggerInterface $usersLogger
     * @param LoggerInterface $endpointLogger
     * @param TranslateService $translateService
     * @param ReportRepository $reportRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/report", name: "apiReport", methods: ["PUT"])]
    #[OA\Put(
        description: "Method used to report for not loged users",
        security: [],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserNotAuthorizedUserReportQuery::class),
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
        Request                 $request,
        RequestServiceInterface $requestService,
        LoggerInterface         $usersLogger,
        LoggerInterface         $endpointLogger,
        TranslateService        $translateService,
        ReportRepository        $reportRepository
    ): Response
    {
        $userNotAuthorizedUserReportQuery = $requestService->getRequestBodyContent($request, UserNotAuthorizedUserReportQuery::class);

        if ($userNotAuthorizedUserReportQuery instanceof UserNotAuthorizedUserReportQuery) {
            if ($reportRepository->notLoggedUserReportsCount($userNotAuthorizedUserReportQuery->getIp()) >= 3) {
                $endpointLogger->error("To many reports from this ip");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("NotLoggedUserToManyReports")]);
            }

            $additionalData = $userNotAuthorizedUserReportQuery->getAdditionalData();
            $actionId = null;
            $description = null;

            if (array_key_exists('actionId', $additionalData)) {
                $actionId = $additionalData['actionId'];
            }
            if (array_key_exists('description', $additionalData)) {
                $description = $additionalData['description'];
            }

            $newReport = new Report($userNotAuthorizedUserReportQuery->getType());

            if ($actionId) {
                $newReport->setActionId($actionId);
            }
            if ($description) {
                $newReport->setDescription($description);
            }

            $reportRepository->add($newReport);

            return ResponseTool::getResponse(httpCode: 201);
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
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
        $userReportQuery = $requestService->getRequestBodyContent($request, UserReportQuery::class);

        if ($userReportQuery instanceof UserReportQuery) {
            return ResponseTool::getResponse(httpCode: 201);
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
        //TODO tu muszę też sprawdzić czy nie robi już za dużo tego samego typu zgłoszeń(max 2)
    }
}
