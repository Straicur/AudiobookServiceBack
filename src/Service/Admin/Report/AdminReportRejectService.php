<?php

declare(strict_types=1);

namespace App\Service\Admin\Report;

use App\Builder\NotificationBuilder;
use App\Entity\Report;
use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Enums\ReportType;
use App\Query\Admin\AdminReportRejectQuery;
use App\Repository\NotificationRepository;
use App\Repository\ReportRepository;
use App\Service\TranslateService;
use DateTime;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class AdminReportRejectService implements AdminReportRejectServiceInterface
{
    private AdminReportRejectQuery $adminReportRejectQuery;
    private Request $request;

    public function __construct(
        private readonly ReportRepository $reportRepository,
        private readonly TagAwareCacheInterface $stockCache,
        private readonly NotificationRepository $notificationRepository,
        private readonly MailerInterface $mailer,
        private readonly TranslateService $translateService,
    ) {
    }

    public function setAdminReportRejectQuery(AdminReportRejectQuery $adminReportRejectQuery): AdminReportRejectService
    {
        $this->adminReportRejectQuery = $adminReportRejectQuery;

        return $this;
    }

    public function setRequest(Request $request): AdminReportRejectService
    {
        $this->request = $request;

        return $this;
    }

    public function sendReportResponseToAll(Report $report): void
    {
        $allReports = $this->reportRepository->findBy([
            "actionId" => $report->getActionId(),
            "accepted" => false,
            "denied"   => false,
        ]);

        foreach ($allReports as $allReport) {
            $this->sendReportResponse($allReport);
        }
    }

    public function sendReportResponse(Report $report): void
    {
        if (!$report->getAccepted() && !$report->getDenied()) {
            $report
                ->setDenied(true)
                ->setAnswer($this->adminReportRejectQuery->getAnswer())
                ->setSettleDate(new DateTime());

            $this->reportRepository->add($report);
        }

        if ($report->getUser()) {
            $notificationBuilder = new NotificationBuilder();

            $notification = $notificationBuilder
                ->setType(NotificationType::USER_REPORT_DENIED)
                ->setAction($report->getId())
                ->addUser($report->getUser())
                ->setUserAction(NotificationUserType::SYSTEM)
                ->setActive(true)
                ->build($this->stockCache);

            $this->notificationRepository->add($notification);
        }

        if ($_ENV['APP_ENV'] !== 'test' && $report->getIp() && $report->getEmail() && $report->getType() !== ReportType::RECRUITMENT_REQUEST) {
            $email = (new TemplatedEmail())
                ->from($_ENV['INSTITUTION_EMAIL'])
                ->to($report->getEmail())
                ->subject($this->translateService->getTranslation('ReportDeniedSubject'))
                ->htmlTemplate('emails/reportDenied.html.twig')
                ->context([
                    'desc'        => $report->getDescription(),
                    'explanation' => $this->adminReportRejectQuery->getAnswer(),
                    'lang'        => $this->request->getPreferredLanguage() !== null ? $this->request->getPreferredLanguage() : $this->translateService->getLocate(),
                ]);
            $this->mailer->send($email);
        }
    }
}
