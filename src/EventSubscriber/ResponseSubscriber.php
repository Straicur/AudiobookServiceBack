<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Repository\AuthenticationTokenRepository;
use App\Repository\TechnicalBreakRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AuthenticationTokenRepository $authenticationTokenRepository,
        private readonly TechnicalBreakRepository $technicalBreakRepository,
        private readonly LoggerInterface $responseLogger,
    ) {
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $authorizationHeaderField = $request->headers->get('authorization');

        $authToken = null;
        if ($authorizationHeaderField !== null) {
            $authToken = $this->authenticationTokenRepository->findActiveToken($authorizationHeaderField);
        }

        $technicalBreak = $this->technicalBreakRepository->findOneBy([
            'active' => true,
        ]);

        if ($technicalBreak !== null) {
            $response->headers->set('Technical-Break', 'true');
        }

        $headersIterator = $response->headers->getIterator();

        $loggerData = [
            'requestUrl'    => $request->getUri(),
            'requestMethod' => $request->getMethod(),
            'user'          => $authToken?->getUser()->getId(),
            'statusCode'    => $response->getStatusCode(),
            'headers'       => $headersIterator->getArrayCopy(),
            'responseData'  => $response->getStatusCode() > 299 ? json_decode($response->getContent(), true) : null,
        ];

        if ($response->getStatusCode() > 499) {
            $this->responseLogger->error('Response data', $loggerData);
        } else {
            $this->responseLogger->info('Response data', $loggerData);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}
