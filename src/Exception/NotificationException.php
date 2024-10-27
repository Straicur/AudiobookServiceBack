<?php

declare(strict_types=1);

namespace App\Exception;

use App\Model\Error\ServiceUnavailableModel;
use App\Tool\ResponseTool;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class NotificationException extends Exception implements ResponseExceptionInterface
{
    private string $serviceName;

    private array $serviceData;

    public function __construct(string $serviceName, array $serviceData = [])
    {
        parent::__construct('Service unavailable');

        $this->serviceName = $serviceName;
        $this->serviceData = $serviceData;
    }

    public function getResponse(): Response
    {
        $serviceDataArray = [
            'serviceName' => $this->serviceName,
            'serviceData' => $this->serviceData
        ];

        return ResponseTool::getResponse(new ServiceUnavailableModel($serviceDataArray), Response::HTTP_SERVICE_UNAVAILABLE);
    }
}
