<?php

namespace MetaFox\Marketplace\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Marketplace\Models\Listing;

/**
 * Class FeedDeletedListener.
 * @ignore
 */
class FeedDeletedListener
{
    /**
     * @param Model $model
     */
    public function handle(Model $model): void
    {
        if ($model instanceof Listing) {
            $model->delete();
        }
    }
}
