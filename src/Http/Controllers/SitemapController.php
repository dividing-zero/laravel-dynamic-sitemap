<?php

namespace DividingZero\LaravelDynamicSitemap\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;
use Cache;
use Carbon\Carbon;
use XMLWriter;

class SitemapController
{
    /**
     * Output the XML sitemap.
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function index()
    {
        $cacheKey = config('sitemap.cache_key');
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
        // Combine all URLs into a single collection
        $urls = collect(array_merge($this->generateRouteEntries(), $this->generateModelEntries()));

        // Sort URLs by last modified date
        $urls = $urls->sortByDesc('lastmod');

		// Initialize XMLWriter
		$xml = new XMLWriter();
		$xml->openMemory();
		$xml->startDocument('1.0', 'UTF-8');

		// Start urlset
		$xml->startElement('urlset');
		$xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

		// Loop through URLs and write XML elements
		foreach ($urls as $url) {
			$xml->startElement('url');

			// Loc is required
			$xml->writeElement('loc', $url['loc']);

			if (!empty($url['lastmod'])) {
				$xml->writeElement('lastmod', $url['lastmod']);
			}

			if (!empty($url['changefreq'])) {
				$xml->writeElement('changefreq', $url['changefreq']);
			}

            // Use is_numeric so that 0 or 0.0 is not skipped
            if (is_numeric($url['priority'])) {
                // number_format ensures 1.0 instead of 1
                $xml->writeElement('priority', number_format($url['priority'], 1));
            }

			$xml->endElement(); // </url>
		}

		// End urlset and document
		$xml->endElement(); // </urlset>
		$xml->endDocument();

		// Return the generated XML as a string
		return $xml->outputMemory();
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

            // Strip parameters from the URL e.g. 'example/{id}' becomes 'example'
            $url = preg_replace('/\\{[^}]+\\}/', '', $route->uri());
            // Remove any double slashes that may result
            $url = trim(preg_replace('/\\/+/', '/', $url), '/');
            // Get full URL
            $url = url($url);

            $urls[$url] = [
                'loc' => $url,
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
                        $url = $item->getSitemapUrl();

                        $urls[$url] = [
                            'loc' => $url,
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
