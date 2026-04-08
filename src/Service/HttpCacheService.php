<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Sets HTTP cache headers for optimal browser caching
 */
class HttpCacheService
{
    /**
     * Add cache headers to response
     */
    public function setCacheHeaders(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // Don't cache redirects or error pages
        if ($response->getStatusCode() >= 300) {
            $response->setMaxAge(0);
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            return;
        }

        // Cache static assets for 1 year
        if (preg_match('#\.(js|css|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$#i', $path)) {
            $response->setMaxAge(31536000); // 1 year
            $response->setSharedMaxAge(31536000);
            $response->headers->addCacheControlDirective('public', true);
            $response->headers->addCacheControlDirective('immutable', true);
            return;
        }

        // Cache product pages for 1 hour
        if (preg_match('#^/shop#', $path) || preg_match('#^/product#', $path)) {
            $response->setMaxAge(3600); // 1 hour
            $response->setSharedMaxAge(3600);
            $response->headers->addCacheControlDirective('public', true);
            return;
        }

        // Cache blog pages for 24 hours
        if (preg_match('#^/blog#', $path)) {
            $response->setMaxAge(86400); // 24 hours
            $response->setSharedMaxAge(86400);
            $response->headers->addCacheControlDirective('public', true);
            return;
        }

        // Don't cache authenticated pages
        if ($request->getSession() && $request->getSession()->get('_security.main.token')) {
            $response->setMaxAge(0);
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('private', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            return;
        }

        // Default: cache for 10 minutes
        $response->setMaxAge(600);
        $response->setSharedMaxAge(600);
        $response->headers->addCacheControlDirective('public', true);
    }
}
