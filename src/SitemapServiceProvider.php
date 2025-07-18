<?php

namespace DividingZero\LaravelDynamicSitemap;

use Illuminate\Support\ServiceProvider;

class SitemapServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register routes and views
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-dynamic-sitemap');

        // Register Sitemappable middleware
        $this->app['router']->aliasMiddleware('sitemappable', \DividingZero\LaravelDynamicSitemap\Http\Middleware\Sitemappable::class);

        // Make config publishable via 'config' tag
        $this->publishes([
            __DIR__.'/../config/dynamic-sitemap.php' => config_path('dynamic-sitemap.php'),
        ], 'config');

        // Make views publishable via 'views' tag
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-dynamic-sitemap'),
        ], 'views');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/dynamic-sitemap.php',
            'dynamic-sitemap'
        );
    }
}
