<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PageCacheMiddleware
{
    /**
     * Lifetime of cached pages in seconds (default: 1 hour)
     */
    private int $ttl = 3600;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only cache GET requests that are not to /api or have query param no_cache
        if ($request->method() !== 'GET' || $request->is('api/*') || $request->query('no_cache')) {
            return $this->compressResponse($next($request), $request);
        }

        $cacheKey = 'page_cache:' . sha1($request->fullUrl());

        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            $response = new Response($cached['content'], 200, $cached['headers']);
            return $this->compressResponse($response, $request, false); // already cached (possibly compressed)
        }

        $response = $next($request);

        if ($response->getStatusCode() === 200) {
            // Store both content and headers to keep things like content-type
            Cache::put($cacheKey, [
                'content' => $response->getContent(),
                'headers' => $response->headers->allPreserveCase(),
            ], $this->ttl);
        }

        return $this->compressResponse($response, $request);
    }

    /**
     * Compress response with gzip if the client supports it.
     */
    private function compressResponse(Response $response, Request $request, bool $encode = true): Response
    {
        if ($response->headers->has('Content-Encoding')) {
            return $response; // already encoded
        }

        if (!str_contains($request->header('Accept-Encoding', ''), 'gzip')) {
            return $response; // client does not accept gzip
        }

        if ($encode) {
            $response->setContent(gzencode($response->getContent(), 6));
        }

        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Vary', 'Accept-Encoding');
        $response->headers->set('Cache-Control', 'public, max-age=' . $this->ttl);

        return $response;
    }
}
