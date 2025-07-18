<?php

use Illuminate\Support\Facades\Route;
use DividingZero\LaravelDynamicSitemap\Http\Controllers\SitemapController;

Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');