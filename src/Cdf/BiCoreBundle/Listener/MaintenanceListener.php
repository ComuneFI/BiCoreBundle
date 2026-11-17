<?php

declare(strict_types=1);

namespace Cdf\BiCoreBundle\Listener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceListener
{
    public function __construct(private readonly string $lockFilePath)
    {
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!file_exists($this->lockFilePath)) {
            return;
        }
        $contentfilelock = file_get_contents($this->lockFilePath);
        if ($contentfilelock) {
            $message = $contentfilelock;
        } else {
            $message = 'Il sistema è in manutenzione, riprovare tra poco...';
        }
        $response = new Response($message, Response::HTTP_SERVICE_UNAVAILABLE);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}
