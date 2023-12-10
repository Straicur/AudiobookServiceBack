<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Builder\NotificationBuilder;
use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Exception\NotificationException;
use App\Model\Admin\AdminReportListSuccessModel;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Query\Admin\AdminReportAcceptQuery;
use App\Query\Admin\AdminReportListQuery;
use App\Query\Admin\AdminReportRejectQuery;
use App\Repository\NotificationRepository;
use App\Repository\ReportRepository;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Service\TranslateService;
use App\Tool\ResponseTool;
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
class AdminReportController extends AbstractController
{
    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param TranslateService $translateService
     * @param ReportRepository $reportRepository
     * @param MailerInterface $mailer
     * @param NotificationRepository $notificationRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     * @throws NotificationException
     * @throws TransportExceptionInterface
     */
    #[Route("/api/report/admin/accept", name: "apiAdminReportAccept", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Patch(
        description: "Endpoint is used to accept report",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminReportAcceptQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function apiReportAdminAccept(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        TranslateService               $translateService,
        ReportRepository               $reportRepository,
        MailerInterface                $mailer,
        NotificationRepository         $notificationRepository
    ): Response
    {
        $adminReportAcceptQuery = $requestService->getRequestBodyContent($request, AdminReportAcceptQuery::class);

        if ($adminReportAcceptQuery instanceof AdminReportAcceptQuery) {
            $report = $reportRepository->findOneBy([
                "id" => $adminReportAcceptQuery->getReportId()
            ]);

            if ($report === null) {
                $endpointLogger->error("Cant find report");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserToManyReports")]);
            }

            if (!$report->getAccepted() && !$report->getDenied()) {
                $report->setAccepted(true);
                $reportRepository->add($report);
            }

            if ($report->getUser()) {
                $notificationBuilder = new NotificationBuilder();

                $notification = $notificationBuilder
                    ->setType(NotificationType::USER_REPORT_ACCEPTED)
                    ->setAction($report->getId())
                    ->addUser($report->getUser())
                    ->setUserAction(NotificationUserType::SYSTEM)
                    ->build();

                $notificationRepository->add($notification);
            }

            if ($report->getIp() && $report->getEmail() && $_ENV["APP_ENV"] !== "test") {
                $email = (new TemplatedEmail())
                    ->from($_ENV["INSTITUTION_EMAIL"])
                    ->to($report->getEmail())
                    ->subject($translateService->getTranslation("ReportAcceptSubject"))
                    ->htmlTemplate('emails/reportAccepted.html.twig')
                    ->context([
                        "desc" => $report->getDescription(),
                    ]);
                $mailer->send($email);
            }

            return ResponseTool::getResponse();
        }

        $endpointLogger->error("Invalid given Query");
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
     * @param MailerInterface $mailer
     * @param NotificationRepository $notificationRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     * @throws NotificationException
     * @throws TransportExceptionInterface
     */
    #[Route("/api/report/admin/reject", name: "apiAdminReportReject", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is used to reject report",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminReportRejectQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function apiReportAdminReject(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        TranslateService               $translateService,
        ReportRepository               $reportRepository,
        MailerInterface                $mailer,
        NotificationRepository         $notificationRepository
    ): Response
    {
        $adminReportRejectQuery = $requestService->getRequestBodyContent($request, AdminReportRejectQuery::class);

        if ($adminReportRejectQuery instanceof AdminReportRejectQuery) {
            $report = $reportRepository->findOneBy([
                "id" => $adminReportRejectQuery->getReportId()
            ]);

            if ($report === null) {
                $endpointLogger->error("Cant find report");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("UserToManyReports")]);
            }

            if (!$report->getAccepted() && !$report->getDenied()) {
                $report->setDenied(true);
                $reportRepository->add($report);
            }

            if ($report->getUser()) {
                $notificationBuilder = new NotificationBuilder();

                $notification = $notificationBuilder
                    ->setType(NotificationType::USER_REPORT_DENIED)
                    ->setAction($report->getId())
                    ->addUser($report->getUser())
                    ->setUserAction(NotificationUserType::SYSTEM)
                    ->build();

                $notificationRepository->add($notification);
            }

            if ($report->getIp() && $report->getEmail() && $_ENV["APP_ENV"] !== "test") {
                $email = (new TemplatedEmail())
                    ->from($_ENV["INSTITUTION_EMAIL"])
                    ->to($report->getEmail())
                    ->subject($translateService->getTranslation("ReportDeniedSubject"))
                    ->htmlTemplate('emails/reportDenied.html.twig')
                    ->context([
                        "desc" => $report->getDescription(),
                        "explanation" => $adminReportRejectQuery->getResponse(),
                    ]);
                $mailer->send($email);
            }
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param TranslateService $translateService
     * @return Response
     */
    #[Route("/api/report/admin/list", name: "apiReportAdminList", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is used to get report list",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminReportListQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AdminReportListSuccessModel::class)
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
        $adminReportListQuery = $requestService->getRequestBodyContent($request, AdminReportListQuery::class);

        if ($adminReportListQuery instanceof AdminReportListQuery) {

        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

}