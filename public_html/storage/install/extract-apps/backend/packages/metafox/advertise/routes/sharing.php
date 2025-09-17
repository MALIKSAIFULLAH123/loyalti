<?php

use Illuminate\Support\Facades\Route;

Route::get('advertise/invoice/detail/{id}', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.advertise.advertise_invoice_detail',
        'advertise_invoice',
        $id,
        function ($data, $resource) use ($id) {
            $data->addBreadcrumb(__p('advertise::phrase.manage_invoices'), '/advertise/invoice/browse');
            $data->addBreadcrumb($resource?->item->title, null);
        }
    );
});
