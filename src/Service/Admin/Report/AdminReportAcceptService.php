<?php

declare(strict_types = 1);

namespace App\Service\Admin\Report;

use App\Builder\NotificationBuilder;
use App\Entity\Report;
use App\Entity\User;
use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Enums\ReportType;
use App\Query\Admin\AdminReportAcceptQuery;
use App\Repository\NotificationRepository;
use App\Repository\ReportRepository;
use App\Service\TranslateServiceInterface;
use DateTime;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class AdminReportAcceptService implements AdminReportAcceptServiceInterface
{
    private AdminReportAcceptQuery $adminReportAcceptQuery;

    private Request $request;

    public function __construct(
        private readonly ReportRepository $reportRepository,
        private readonly TagAwareCacheInterface $stockCache,
        private readonly NotificationRepository $notificationRepository,
        private readonly MailerInterface $mailer,
        private readonly TranslateServiceInterface $translateService,
        #[Autowire(env: 'INSTITUTION_EMAIL')] private readonly string $institutionEmail,
        #[Autowire(env: 'bool:SEND_EMAIL')] private readonly bool $sendEmail,
    ) {}

    public function setAdminReportAcceptQuery(AdminReportAcceptQuery $adminReportAcceptQuery): AdminReportAcceptService
    {
        $this->adminReportAcceptQuery = $adminReportAcceptQuery;

        return $this;
    }

    public function setRequest(Request $request): AdminReportAcceptService
    {
        $this->request = $request;

        return $this;
    }

    public function sendReportResponseToAll(Report $report): void
    {
        $allReports = $this->reportRepository->findBy([
            'actionId' => $report->getActionId(),
            'accepted' => false,
            'denied'   => false,
        ]);

        foreach ($allReports as $allReport) {
            $this->sendReportResponse($allReport);
        }
    }

    public function sendReportResponse(Report $report): void
    {
        $report
            ->setAccepted(true)
            ->setAnswer($this->adminReportAcceptQuery->getAnswer())
            ->setSettleDate(new DateTime());

        $this->reportRepository->add($report);

        if ($report->getUser() instanceof User) {
            $notificationBuilder = new NotificationBuilder();

            $notification = $notificationBuilder
                ->setType(NotificationType::USER_REPORT_ACCEPTED)
                ->setAction($report->getId())
                ->addUser($report->getUser())
                ->setUserAction(NotificationUserType::SYSTEM)
                ->setActive(true)
                ->build($this->stockCache);

            $this->notificationRepository->add($notification);
        }

        if (true === $this->sendEmail && $report->getEmail() && $report->getType() !== ReportType::RECRUITMENT_REQUEST) {
            $email = new TemplatedEmail()
                ->from($this->institutionEmail)
                ->to($report->getEmail())
                ->subject($this->translateService->getTranslation('ReportAcceptSubject'))
                ->htmlTemplate('emails/reportAccepted.html.twig')
                ->context([
                    'desc'   => $report->getDescription(),
                    'answer' => $this->adminReportAcceptQuery->getAnswer(),
                    'lang'   => $this->request->getPreferredLanguage() ?? $this->translateService->getLocate(),
                ]);
            $this->mailer->send($email);
        }
    }
}
