<?php

namespace App\Exception;

use App\Model\ServiceUnavailableModel;
use App\Tool\ResponseTool;
use Symfony\Component\HttpFoundation\Response;


class NotificationException extends \Exception implements ResponseExceptionInterface
{
    private string $serviceName;

    private array $serviceData;

    public function __construct(string $serviceName, array $serviceData = [])
    {
        parent::__construct("Service unavailable");

        $this->serviceName = $serviceName;
        $this->serviceData = $serviceData;
    }

    public function getResponse(): Response
    {
        $serviceDataArray = [
            "serviceName" => $this->serviceName,
            "serviceData" => $this->serviceData
        ];

        return ResponseTool::getResponse(new ServiceUnavailableModel($serviceDataArray), 503);
    }
}