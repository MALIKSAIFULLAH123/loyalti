<?php

use Illuminate\Support\Facades\Route;

Route::get('subscription/invoice/detail/{id}', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.subscription.subscription_invoice_detail',
        'subscription_invoice',
        $id,
        function ($data, $resource) use ($id) {
            $data->addBreadcrumb(__p('subscription::admin.manage_subscriptions'), '/subscription/invoice/browse');
            $data->addBreadcrumb($resource?->package->title, null);
        }
    );
});
