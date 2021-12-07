<?php

namespace Cdf\BiCoreBundle\Listener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceListener
{
    private string $lockFilePath;

    public function __construct(string $lockFilePath)
    {
        $this->lockFilePath = $lockFilePath;
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
