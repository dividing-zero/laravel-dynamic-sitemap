<?php

namespace DividingZero\LaravelDynamicSitemap\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;
use Cache;
use Carbon\Carbon;

class SitemapController
{
    /**
     * Output the XML sitemap.
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function index()
    {
        $cacheKey = 'laravel_dynamic-sitemap_sitemap_xml';
        $cacheSeconds = config('sitemap.cache_lifetime');

        // Get XML from cache if enabled
        if ($cacheSeconds) {
            $xml = Cache::remember($cacheKey, $cacheSeconds, function () {
                return $this->generateSitemapXml();
            });
        } else {
            $xml = $this->generateSitemapXml();
        }

        // Return the XML response
        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * Generate the XML sitemap content.
     *
     * @return string
     */
    private function generateSitemapXml()
    {
        $urls = [
            ...$this->generateRouteEntries(),
            ...$this->generateModelEntries()
        ];

        return view('laravel-dynamic-sitemap::sitemap', ['urls' => $urls])->render();
    }

    /**
     * Generate sitemap entries for all routes which have the 'sitemappable' middleware and are GET requests
     *
     * @return array
     */
    private function generateRouteEntries()
    {
        $urls = [];
        $routes = app('router')->getRoutes();

        // Loop through all routes and append URL entries
        foreach ($routes as $route) {

            // Only include GET routes
            if (!in_array('GET', $route->methods())) {
                continue;
            }

            // Only include routes with the 'sitemappable' middleware
            try {
                $routeMiddleware = $route->gatherMiddleware();
            } catch (\Throwable $e) {
                continue;
            }
            if (!in_array('sitemappable', $routeMiddleware)) {
                continue;
            }

            $urls[] = [
                'loc' => url($route->uri()),
                'lastmod' => Carbon::parse(config('sitemap.default_modified_date'))->toAtomString(),
                'changefreq' => config('sitemap.default_change_frequency'),
                'priority' => config('sitemap.default_priority')
            ];
        }

        return $urls;
    }

    /**
     * Generate sitemap entries for all models defined in the configuration which implement the Sitemappable trait.
     *
     * @return array
     */
    private function generateModelEntries()
    {
        $models = config('sitemap.models');
        $urls = [];

        // Loop through configured model classes
        foreach ($models as $modelClass) {
            if (class_exists($modelClass)) {
                // Get all instances of the model that should be included in the sitemap via the sitemapQuery query scope
                $items = App::make($modelClass)::sitemapQuery()->get();

                // Loop through each model instance and append URL entries
                foreach ($items as $item) {
                    if (method_exists($item, 'getSitemapUrl')) {
                        $urls[] = [
                            'loc' => $item->getSitemapUrl(),
                            'lastmod' => $item->getSitemapModifiedDate()->toAtomString(),
                            'changefreq' => $item->getSitemapChangeFreqency(),
                            'priority' => $item->getSitemapPriority(),
                        ];
                    }
                }
            }
        }

        return $urls;
    }
}
