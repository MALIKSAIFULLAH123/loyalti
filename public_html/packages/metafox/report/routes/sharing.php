<?php

use Illuminate\Support\Facades\Route;
use MetaFox\Platform\Contracts\Content;
use Illuminate\Support\Str;

Route::get('report/aggregate/{id}/item/browse', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.report.browse_report_item',
        'report_item_aggregate',
        $id,
        function ($data, $aggregate) use ($id) {
            $item      = $aggregate?->item;
            $itemType  = __p('report::phrase.report_item_aggregate_id', ['id' => $id]);
            $title     = null;

            if ($item instanceof Content) {
                $itemType = Str::headline(__p_type_key($item->entityType()));
                $title    = $item->toTitle();
            }

            $data->addBreadcrumb($itemType, null);
        }
    );
});
