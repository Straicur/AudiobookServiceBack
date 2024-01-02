<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Builder\NotificationBuilder;
use App\Entity\UserBanHistory;
use App\Enums\BanPeriodRage;
use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Enums\ReportType;
use App\Enums\UserBanAmount;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Exception\NotificationException;
use App\Model\Admin\AdminReportListSuccessModel;
use App\Model\Admin\AdminReportModel;
use App\Model\Admin\AdminUserModel;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Query\Admin\AdminReportAcceptQuery;
use App\Query\Admin\AdminReportListQuery;
use App\Query\Admin\AdminReportRejectQuery;
use App\Repository\AudiobookUserCommentRepository;
use App\Repository\NotificationRepository;
use App\Repository\ReportRepository;
use App\Repository\UserBanHistoryRepository;
use App\Repository\UserDeleteRepository;
use App\Repository\UserRepository;
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
     * @param AudiobookUserCommentRepository $commentRepository
     * @param UserRepository $userRepository
     * @param UserBanHistoryRepository $banHistoryRepository
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
        NotificationRepository         $notificationRepository,
        AudiobookUserCommentRepository $commentRepository,
        UserRepository                 $userRepository,
        UserBanHistoryRepository       $banHistoryRepository,
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

            if ($report->getActionId() !== null && $report->getType() === ReportType::COMMENT) {
                $comment = $commentRepository->findOneBy([
                    "id" => $report->getActionId()
                ]);

                if ($comment !== null) {
                    $user = $comment->getUser();
                    $user->setBanned(true);

                    if ($adminReportAcceptQuery->getBanPeriod() === BanPeriodRage::SYSTEM) {
                        $bannedAmount = count($banHistoryRepository->findBy([
                            "user" => $user->getId()
                        ]));

                        if ($bannedAmount === UserBanAmount::NONE->value) {
                            $periodTo = BanPeriodRage::HALF_DAY_BAN->value;
                        } elseif ($bannedAmount > 0 && $bannedAmount <= UserBanAmount::LOW->value) {
                            $periodTo = BanPeriodRage::ONE_DAY_BAN->value;
                        } elseif ($bannedAmount > UserBanAmount::LOW->value && $bannedAmount <= UserBanAmount::MEDIUM->value) {
                            $periodTo = BanPeriodRage::FIVE_DAY_BAN->value;
                        } elseif ($bannedAmount > UserBanAmount::MEDIUM->value && $bannedAmount <= UserBanAmount::HIGH->value) {
                            $periodTo = BanPeriodRage::ONE_MONTH_BAN->value;
                        } else {
                            $periodTo = BanPeriodRage::ONE_YEAR_BAN->value;
                        }
                    } else {
                        $periodTo = $adminReportAcceptQuery->getBanPeriod()->value;
                    }

                    $banPeriod = (new \DateTime('Now'))->modify($periodTo);

                    $user->setBannedTo($banPeriod);

                    if($periodTo !== BanPeriodRage::NOT_BANNED->value){
                        $user->setBanned(true);
                    }

                    $userRepository->add($user);
                    $banHistoryRepository->add(new UserBanHistory($user, new \DateTime('Now'), $banPeriod));

                    if ($user->getUserInformation()->getEmail() && $_ENV["APP_ENV"] !== "test") {
                        $email = (new TemplatedEmail())
                            ->from($_ENV["INSTITUTION_EMAIL"])
                            ->to($report->getEmail())
                            ->subject($translateService->getTranslation("UserBannedSubject"))
                            ->htmlTemplate('emails/userBanned.html.twig')
                            ->context([
                                "name" => $user->getUserInformation()->getFirstname(),
                                "desc" => $comment->getComment(),
                                "dateTo" => $banPeriod->format('d.m.Y'),
                            ]);
                        $mailer->send($email);
                    }
                }
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
     * @param UserDeleteRepository $userDeleteRepository
     * @return Response
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/report/list", name: "apiAdminReportList", methods: ["POST"])]
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
    public function apiAdminReportList(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        TranslateService               $translateService,
        ReportRepository               $reportRepository,
        UserDeleteRepository           $userDeleteRepository
    ): Response
    {
        $adminReportListQuery = $requestService->getRequestBodyContent($request, AdminReportListQuery::class);

        if ($adminReportListQuery instanceof AdminReportListQuery) {
            $reportSearchData = $adminReportListQuery->getSearchData();

            $actionId = null;
            $desc = null;
            $email = null;
            $ip = null;
            $type = null;
            $user = null;
            $accepted = null;
            $denied = null;
            $dateFrom = null;
            $dateTo = null;
            $order = null;

            if (array_key_exists('desc', $reportSearchData)) {
                $desc = ($reportSearchData['desc'] && '' != $reportSearchData['desc']) ? "%" . $reportSearchData['desc'] . "%" : null;
            }
            if (array_key_exists('email', $reportSearchData)) {
                $email = ($reportSearchData['email'] && '' != $reportSearchData['email']) ? "%" . $reportSearchData['email'] . "%" : null;
            }
            if (array_key_exists('ip', $reportSearchData)) {
                $ip = ($reportSearchData['ip'] && '' != $reportSearchData['ip']) ? "%" . $reportSearchData['ip'] . "%" : null;
            }
            if (array_key_exists('actionId', $reportSearchData)) {
                $actionId = $reportSearchData['actionId'];
            }
            if (array_key_exists('type', $reportSearchData)) {
                $type = $reportSearchData['type'];
            }
            if (array_key_exists('user', $reportSearchData)) {
                $user = $reportSearchData['user'];
            }
            if (array_key_exists('accepted', $reportSearchData)) {
                $accepted = $reportSearchData['accepted'];
            }
            if (array_key_exists('denied', $reportSearchData)) {
                $denied = $reportSearchData['denied'];
            }
            if (array_key_exists('order', $reportSearchData)) {
                $order = $reportSearchData['order'];
            }
            if (array_key_exists('dateFrom', $reportSearchData) && $reportSearchData['dateFrom']) {
                $dateFrom = $reportSearchData['dateFrom'];
            }
            if (array_key_exists('dateTo', $reportSearchData) && $reportSearchData['dateTo']) {
                $dateTo = $reportSearchData['dateTo'];
            }

            $successModel = new AdminReportListSuccessModel();

            $reports = $reportRepository->getReportsByPage($actionId, $desc, $email, $ip, $type, $user, $accepted, $denied, $dateFrom, $dateTo, $order);

            $minResult = $adminReportListQuery->getPage() * $adminReportListQuery->getLimit();
            $maxResult = $adminReportListQuery->getLimit() + $minResult;

            foreach ($reports as $index => $report) {
                if ($index < $minResult) {
                    continue;
                }

                if ($index < $maxResult) {
                    $reportModel = new AdminReportModel(
                        $report->getId(),
                        $report->getType(),
                        $report->getDateAdd(),
                        $report->getAccepted(),
                        $report->getDenied()
                    );
                    if ($report->getDescription()) {
                        $reportModel->setDescription($report->getDescription());
                    }
                    if ($report->getActionId()) {
                        $reportModel->setActionId($report->getActionId());
                    }
                    if ($report->getEmail()) {
                        $reportModel->setEmail($report->getEmail());
                    }
                    if ($report->getIp()) {
                        $reportModel->setIp($report->getIp());
                    }
                    if ($report->getUser()) {

                        $userDeleted = $userDeleteRepository->userInToDeleteList($report->getUser());

                        $reportModel->setUser(
                            new AdminUserModel(
                                $report->getUser()->getId(),
                                $report->getUser()->isActive(),
                                $report->getUser()->isBanned(),
                                $report->getUser()->getUserInformation()->getEmail(),
                                $report->getUser()->getUserInformation()->getFirstname(),
                                $report->getUser()->getUserInformation()->getLastname(),
                                $report->getUser()->getDateCreate(),
                                $userDeleted
                            )
                        );
                    }
                    $successModel->addReport($reportModel);
                } else {
                    break;
                }
            }

            $successModel->setPage($adminReportListQuery->getPage());
            $successModel->setLimit($adminReportListQuery->getLimit());
            $successModel->setMaxPage(ceil(count($reports) / $adminReportListQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

}