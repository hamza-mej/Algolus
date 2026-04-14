<?php

namespace App\Service;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Adds security headers to all responses
 */
class SecurityHeadersService
{
    public function addSecurityHeaders(ResponseEvent $event): void
    {
        // Only process main request
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();

        // Prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS filter
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Force HTTPS in production (requires reverse proxy to set X-Forwarded-Proto)
        if ('prod' === ($_ENV['APP_ENV'] ?? 'dev')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Content Security Policy - disabled as it blocks external resources
        // If needed, configure per-route or for production only
        // $csp = "default-src 'self'; "
        //     . "script-src 'self' 'unsafe-inline' 'unsafe-eval'; "
        //     . "style-src 'self' 'unsafe-inline'; "
        //     . "img-src 'self' data: https:; "
        //     . "font-src 'self' data:; "
        //     . "connect-src 'self'";
        // $response->headers->set('Content-Security-Policy', $csp);

        // Prevent browsers from MIME-sniffing a response away from the declared Content-Type
        $response->headers->set('X-Content-Type-Options', 'nosniff');
    }
}
