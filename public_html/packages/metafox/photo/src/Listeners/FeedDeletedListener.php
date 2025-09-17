<?php

namespace MetaFox\Photo\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Models\PhotoGroup;

class FeedDeletedListener
{
    public function handle(Model $model): void
    {
        if ($model instanceof PhotoGroup || $model instanceof Photo) {
            $model->delete();
        }
    }
}
