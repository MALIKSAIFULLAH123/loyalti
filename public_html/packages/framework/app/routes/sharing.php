<?php

use Illuminate\Support\Facades\Route;

Route::get('app/store/product/{id}', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.app.product_detail',
        null,
        null,
        function ($meta, $resource) use ($id) {
            $store   = resolve(\MetaFox\App\Support\MetaFoxStore::class);
            $product = $store->show($id);
            $label   = isset($product['name']) ? $product['name'] : 'Store App #' . $id;

            if (!$meta instanceof \MetaFox\SEO\SeoMetaData) {
                return;
            }

            $meta->addBreadcrumb(__p('app::phrase.store'), '/app/store/products/browse');
            $meta->addBreadcrumb($label, null);
            $title = $meta->offsetGet('title');

            $meta->offsetSet('title', str_replace(__p('app::phrase.product_name'), $label, $title));
        }
    );
});
