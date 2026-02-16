<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Very permissive CORS subscriber that will allow cross-origin requests from any origin.
 *
 * Notes:
 * - If the request has an Origin header we echo it back as Access-Control-Allow-Origin.
 *   This allows credentials while remaining permissive.
 * - This subscriber also handles OPTIONS preflight requests and returns a 204 response.
 */
final class CorsSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        // Handle preflight on REQUEST with high priority so it returns before controllers run
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 100],
            KernelEvents::RESPONSE => ['onKernelResponse', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Only handle master requests
        if (! $event->isMainRequest()) {
            return;
        }

        // If this is a preflight OPTIONS request, return an empty successful response
        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            $response = new Response();
            $this->addCorsHeaders($request, $response);
            $response->setStatusCode(Response::HTTP_NO_CONTENT);

            $event->setResponse($response);
            // Stop propagation so no controller is executed
            $event->stopPropagation();
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (! $event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        $this->addCorsHeaders($request, $response);
    }

    private function addCorsHeaders(\Symfony\Component\HttpFoundation\Request $request, Response $response): void
    {
        $origin = $request->headers->get('Origin');

        // If origin is present, echo it back. Otherwise allow all origins.
        if ($origin) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            // Make sure caches vary by origin
            $response->headers->set('Vary', 'Origin');
        } else {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }

        // Allow common HTTP methods
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD');

        // Allow requested headers or a permissive set
        $requestHeaders = $request->headers->get('Access-Control-Request-Headers');
        if ($requestHeaders) {
            $response->headers->set('Access-Control-Allow-Headers', $requestHeaders);
        } else {
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-Token, Accept');
        }

        // Allow credentials (cookies, auth) — note: Access-Control-Allow-Origin cannot be '*' when this is true,
        // which is why we echo the Origin header when present.
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        // Cache preflight responses for a while
        $response->headers->set('Access-Control-Max-Age', '3600');
    }
}

