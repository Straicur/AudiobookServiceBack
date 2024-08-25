<?php

namespace App\Service\Admin;

use App\Entity\Report;
use App\Query\Admin\AdminReportAcceptQuery;
use Symfony\Component\HttpFoundation\Request;

interface AdminReportAcceptServiceInterface
{
    public function setAdminReportAcceptQuery(AdminReportAcceptQuery $adminReportAcceptQuery): AdminReportAcceptService;

    public function setRequest(Request $request): AdminReportAcceptService;

    public function sendReportResponseToAll(Report $report): void;

    public function sendReportResponse(Report $report): void;
}
