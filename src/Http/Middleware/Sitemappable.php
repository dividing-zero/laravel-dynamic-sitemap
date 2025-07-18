<?php

namespace DividingZero\LaravelDynamicSitemap\Http\Middleware;

use Closure;

class Sitemappable
{
    public function handle($request, Closure $next)
    {
        // This middleware is just a marker for sitemappable routes
        return $next($request);
    }
}
