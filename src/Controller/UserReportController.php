<?php

declare(strict_types=1);

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
use Symfony\Component\Routing\Attribute\Route;

#[OA\Response(
    response: 400,
    description: 'JSON Data Invalid',
    content: new Model(type: JsonDataInvalidModel::class)
)]
#[OA\Response(
    response: 404,
    description: 'Data not found',
    content: new Model(type: DataNotFoundModel::class)
)]
#[OA\Response(
    response: 401,
    description: 'User not authorized',
    content: new Model(type: NotAuthorizeModel::class)
)]
#[OA\Response(
    response: 403,
    description: 'User have no permission',
    content: new Model(type: PermissionNotGrantedModel::class)
)]
#[OA\Tag(name: 'UserReport')]
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
    #[Route('/api/report', name: 'apiReport', methods: ['PUT'])]
    #[OA\Put(
        description: 'Method used to report for not logged users',
        security: [],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserNotAuthorizedUserReportQuery::class),
                type: 'object'
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Success',
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
            $ip = $userNotAuthorizedUserReportQuery->getIp();
            $amountOfReports = $reportRepository->notLoggedUserReportsCount($ip);

            if ($amountOfReports[array_key_first($amountOfReports)] >= 3) {
                $endpointLogger->error('To many reports from this ip');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserToManyReports')]);
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
            $newReport->setIp($ip);
            $newReport->setEmail($userNotAuthorizedUserReportQuery->getEmail());

            if ($actionId) {
                $newReport->setActionId($actionId);
            }
            if ($description) {
                $newReport->setDescription($description);
            }

            $reportRepository->add($newReport);

            return ResponseTool::getResponse(httpCode: 201);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param TranslateService $translateService
     * @param ReportRepository $reportRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route('/api/report/user', name: 'apiUserReport', methods: ['PUT'])]
    #[AuthValidation(checkAuthToken: true, roles: ['User'])]
    #[OA\Put(
        description: 'Endpoint is used for users to report bad behavior',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserReportQuery::class),
                type: 'object'
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Success',
            )
        ]
    )]
    public function apiReportUser(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        TranslateService               $translateService,
        ReportRepository               $reportRepository
    ): Response
    {
        $userReportQuery = $requestService->getRequestBodyContent($request, UserReportQuery::class);

        if ($userReportQuery instanceof UserReportQuery) {
            $user = $authorizedUserService->getAuthorizedUser();
            $amountOfReports = $reportRepository->loggedUserReportsCount($user);

            if ($amountOfReports[array_key_first($amountOfReports)] >= 3) {
                $endpointLogger->error('To many reports from this ip');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserToManyReports')]);
            }

            $additionalData = $userReportQuery->getAdditionalData();
            $actionId = null;
            $description = null;

            if (array_key_exists('actionId', $additionalData)) {
                $actionId = $additionalData['actionId'];
            }
            if (array_key_exists('description', $additionalData)) {
                $description = $additionalData['description'];
            }

            $newReport = new Report($userReportQuery->getType());
            $newReport->setUser($user);

            if ($actionId) {
                $newReport->setActionId($actionId);
            }
            if ($description) {
                $newReport->setDescription($description);
            }

            $reportRepository->add($newReport);

            return ResponseTool::getResponse(httpCode: 201);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }
}
