<?php

namespace App\EventSubscriber;

use App\Exception\DataNotFoundException;
use App\Exception\ResponseExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * AuthValidationSubscriber
 *
 */
class ExceptionSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $responseLogger;

    public function __construct(
        LoggerInterface $responseLogger,
    )
    {
        $this->responseLogger = $responseLogger;
    }

    /**
     * @param ExceptionEvent $event
     * @return void
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof ResponseExceptionInterface) {
            $loggingContext = [
                "statusCode" => $exception->getResponse()->getStatusCode(),
                "file" => "[" . $exception->getLine() . "](" . $exception->getFile() . ")",
                "responseData" => json_decode($exception->getResponse()->getContent(), true)
            ];

            $this->responseLogger->info("ResponseException", $loggingContext);

            $event->setResponse($exception->getResponse());
        } else {
            $this->responseLogger->critical("ResponseException", ["class" => $exception::class, "data" => $exception]);

            switch ($exception::class) {
                case NotFoundHttpException::class:
                {
                    $notFoundException = new DataNotFoundException([$exception->getMessage()]);

                    $event->setResponse($notFoundException->getResponse());
                    break;
                }
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

}