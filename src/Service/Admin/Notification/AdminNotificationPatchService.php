<?php

declare(strict_types=1);

namespace App\Service\Admin\Notification;

use App\Query\Admin\AdminReportAcceptQuery;
use App\Repository\NotificationRepository;
use App\Repository\ReportRepository;
use App\Service\TranslateService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class AdminNotificationPatchService implements AdminNotificationPatchServiceInterface
{
    public function __construct(
        private readonly ReportRepository       $reportRepository,
        private readonly TagAwareCacheInterface $stockCache,
        private readonly NotificationRepository $notificationRepository,
        private readonly MailerInterface        $mailer,
        private readonly TranslateService       $translateService,
    ) {
    }
}