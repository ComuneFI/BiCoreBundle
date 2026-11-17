<?php

declare(strict_types=1);

namespace Cdf\BiCoreBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;

class MaintenanceListener implements EventSubscriberInterface
{
    public function __construct(private readonly string $lockFilePath)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Registriamo l'ascolto sull'evento response del kernel
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!file_exists($this->lockFilePath)) {
            return;
        }
        
        // Assicurati che l'evento provenga dalla richiesta principale (master request)
        if (!$event->isMainRequest()) {
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