<?php

namespace MetaFox\SEO\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::controller(SitemapController::class)
    ->group(function () {
        // generate sitemap index
        Route::get('api/sitemap/index.xml', 'index');

        // generate site map with multiple page
        Route::get('api/sitemap/pages-{page}.xml', 'clientUrls')->whereNumber('page');
        // generate sitemap for single page
        Route::get('api/sitemap/pages.xml', 'clientUrls');
        // generate site map with multiple page
        Route::get('api/sitemap/{name}-{page}.xml', 'urls')->whereNumber('page');
        // generate sitemap for single page
        Route::get('api/sitemap/{name}.xml', 'urls');

        // generate sitemap index
        Route::get('sitemap/index.xml', 'index');
        // generate site map with multiple page
        Route::get('sitemap/pages-{page}.xml', 'clientUrls')->whereNumber('page');
        // generate sitemap for single page
        Route::get('sitemap/pages.xml', 'clientUrls');
        // generate site map with multiple page
        Route::get('sitemap/{name}-{page}.xml', 'urls')->whereNumber('page');
        // generate sitemap for single page
        Route::get('sitemap/{name}.xml', 'urls');
    });
