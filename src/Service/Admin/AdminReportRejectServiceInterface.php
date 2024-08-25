<?php

namespace App\Service\Admin;

use App\Entity\Report;
use App\Query\Admin\AdminReportRejectQuery;
use Symfony\Component\HttpFoundation\Request;

interface AdminReportRejectServiceInterface
{
    public function setAdminReportRejectQuery(AdminReportRejectQuery $adminReportRejectQuery): AdminReportRejectService;

    public function setRequest(Request $request): AdminReportRejectService;

    public function sendReportResponseToAll(Report $report): void;

    public function sendReportResponse(Report $report): void;
}
