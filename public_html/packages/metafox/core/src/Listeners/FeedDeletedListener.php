<?php

namespace MetaFox\Core\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Core\Models\Link;

class FeedDeletedListener
{
    public function handle(Model $model): void
    {
        if ($model instanceof Link) {
            $model->delete();
            $model->shares()->delete();
        }
    }
}
