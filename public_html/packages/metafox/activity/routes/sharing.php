<?php

use Illuminate\Support\Facades\Route;

Route::get('{ownerType}/{ownerId}/feed/{id}', function ($ownerType, $ownerId, $id) {
    return seo_sharing_view(
        'web',
        'feed.feed.view_detail_on_owner',
        'feed',
        $id,
        null
    );
});
