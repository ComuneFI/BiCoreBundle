<?php

namespace Cdf\BiCoreBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceListener
{

    private $lockFilePath;

    public function __construct($lockFilePath)
    {
        $this->lockFilePath = $lockFilePath;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $lockfile = $this->lockFilePath;
        if (!file_exists($lockfile)) {
            return;
        }
        $contentfilelock = file_get_contents($lockfile);
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
