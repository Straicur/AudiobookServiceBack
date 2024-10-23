<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Annotation\AuthValidation;
use App\Entity\Report;
use App\Enums\ReportLimits;
use App\Enums\ReportType;
use App\Enums\UserRolesNames;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Model\User\UserReportListSuccessModel;
use App\Model\User\UserReportModel;
use App\Query\User\UserNotAuthorizedUserReportQuery;
use App\Query\User\UserReportListQuery;
use App\Query\User\UserReportQuery;
use App\Repository\AudiobookUserCommentRepository;
use App\Repository\ReportRepository;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Service\TranslateServiceInterface;
use App\Tool\ResponseTool;
use Nelmio\ApiDocBundle\Annotation\Model;
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
#[OA\Tag(name: 'UserReport')]
#[Route('/api')]
class UserReportController extends AbstractController
{
    #[Route('/report', name: 'apiReport', methods: ['PUT'])]
    #[OA\Put(
        description: 'Method used to report for not logged users',
        security   : [],
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserNotAuthorizedUserReportQuery::class),
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
    public function report(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        TranslateServiceInterface $translateService,
        ReportRepository $reportRepository,
    ): Response {
        $userNotAuthorizedUserReportQuery = $requestService->getRequestBodyContent($request, UserNotAuthorizedUserReportQuery::class);

        if ($userNotAuthorizedUserReportQuery instanceof UserNotAuthorizedUserReportQuery) {
            $ip = $userNotAuthorizedUserReportQuery->getIp();
            $email = $userNotAuthorizedUserReportQuery->getEmail();

            if (!empty($ip)) {
                $amountOfReports = $reportRepository->notLoggedUserReportsCount($ip, $email, $userNotAuthorizedUserReportQuery->getType());

                if ($amountOfReports[array_key_first($amountOfReports)] >= ReportLimits::IP_LIMIT->value) {
                    $endpointLogger->error('To many reports from this ip');
                    $translateService->setPreferredLanguage($request);
                    throw new DataNotFoundException([$translateService->getTranslation('UserToManyReports')]);
                }
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

            if (!empty($ip)) {
                $newReport->setIp($ip);
            }

            $newReport->setEmail($email);

            if ($actionId) {
                $newReport->setActionId($actionId);
            }
            if ($description) {
                $newReport->setDescription($description);
            }
            if ($userNotAuthorizedUserReportQuery->getType() === ReportType::RECRUITMENT_REQUEST) {
                $newReport->setDenied(true);
            }

            $reportRepository->add($newReport);

            return ResponseTool::getResponse(httpCode: Response::HTTP_CREATED);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/user/report', name: 'apiUserReport', methods: ['PUT'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Put(
        description: 'Endpoint is used for users to report bad behavior',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserReportQuery::class),
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
    public function userReport(
        Request $request,
        RequestServiceInterface $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface $endpointLogger,
        TranslateServiceInterface $translateService,
        ReportRepository $reportRepository,
    ): Response {
        $userReportQuery = $requestService->getRequestBodyContent($request, UserReportQuery::class);

        if ($userReportQuery instanceof UserReportQuery) {
            $additionalData = $userReportQuery->getAdditionalData();
            $actionId = null;
            $description = null;

            if (array_key_exists('actionId', $additionalData)) {
                $actionId = $additionalData['actionId'];
            }
            if (array_key_exists('description', $additionalData)) {
                $description = $additionalData['description'];
            }

            $user = $authorizedUserService::getAuthorizedUser();
            $amountOfReports = $reportRepository->loggedUserReportsCount($user, $userReportQuery->getType(), $actionId);

            if ($amountOfReports[array_key_first($amountOfReports)] >= ReportLimits::EMAIL_LIMIT->value) {
                $endpointLogger->error('To many reports from this ip');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('UserToManyReports')]);
            }

            $newReport = new Report($userReportQuery->getType());
            $newReport
                ->setUser($user)
                ->setEmail($user->getUserInformation()->getEmail());

            if ($actionId) {
                $newReport->setActionId($actionId);
            }
            if ($description) {
                $newReport->setDescription($description);
            }
            if ($userReportQuery->getType() === ReportType::RECRUITMENT_REQUEST) {
                $newReport->setDenied(true);
            }

            $reportRepository->add($newReport);

            return ResponseTool::getResponse(httpCode: Response::HTTP_CREATED);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/user/reports', name: 'apiUserReports', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Post(
        description: 'Endpoint returning user reports',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserReportListQuery::class),
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
    public function userReports(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        AuthorizedUserServiceInterface $authorizedUserService,
        ReportRepository $reportRepository,
        TranslateServiceInterface $translateService,
        AudiobookUserCommentRepository $commentRepository,
    ): Response {
        $adminReportListQuery = $requestService->getRequestBodyContent($request, UserReportListQuery::class);

        if ($adminReportListQuery instanceof UserReportListQuery) {
            $user = $authorizedUserService::getAuthorizedUser();

            $reports = $reportRepository->findBy(['user' => $user], ['dateAdd' => 'DESC']);

            $successModel = new UserReportListSuccessModel();

            $minResult = $adminReportListQuery->getPage() * $adminReportListQuery->getLimit();
            $maxResult = $adminReportListQuery->getLimit() + $minResult;

            foreach ($reports as $index => $report) {
                if ($index < $minResult) {
                    continue;
                }

                if ($index < $maxResult) {
                    $reportModel = new UserReportModel(
                        $report->getType(),
                        $report->getDateAdd(),
                        $report->getAccepted(),
                        $report->getDenied(),
                    );

                    if ($report->getDescription()) {
                        $reportModel->setDescription($report->getDescription());
                    }
                    if ($report->getAnswer()) {
                        $reportModel->setAnswer($report->getAnswer());
                    }
                    if ($report->getSettleDate()) {
                        $reportModel->setSettleDate($report->getSettleDate());
                    }

                    if ($report->getType() === ReportType::COMMENT && $report->getActionId() !== null) {
                        $comment = $commentRepository->find($report->getActionId());

                        if ($comment !== null) {
                            $reportModel->setComment($comment->getComment());
                        }
                    }

                    $successModel->addReport($reportModel);
                } else {
                    break;
                }
            }

            $successModel->setPage($adminReportListQuery->getPage());
            $successModel->setLimit($adminReportListQuery->getLimit());
            $successModel->setMaxPage((int)ceil(count($reports) / $adminReportListQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }
}
