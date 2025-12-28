<?php

declare(strict_types = 1);

namespace App\Exception;

use App\Model\Error\ServiceUnavailableModel;
use App\Tool\ResponseTool;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class NotificationException extends Exception implements ResponseExceptionInterface
{
    public function __construct(private readonly string $serviceName, private readonly array $serviceData = [])
    {
        parent::__construct('Service unavailable');
    }

    public function getResponse(): Response
    {
        $serviceDataArray = [
            'serviceName' => $this->serviceName,
            'serviceData' => $this->serviceData,
        ];

        return ResponseTool::getResponse(new ServiceUnavailableModel($serviceDataArray), Response::HTTP_SERVICE_UNAVAILABLE);
    }
}
