<?php

namespace MetaFox\Music\Listeners;

use MetaFox\Music\Models\Album;
use MetaFox\Music\Models\Song;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\User;

class ModelApprovedListener
{
    /**
     * @param  User|null $context
     * @param  Model     $model
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(?User $context, Model $model): void
    {
        if (!$model instanceof Song) {
            return;
        }

        if (!$model->album instanceof Album) {
            return;
        }

        $model->album->incrementAmount('total_track');
        $model->album->incrementAmount('total_duration', $model->duration);
    }
}
