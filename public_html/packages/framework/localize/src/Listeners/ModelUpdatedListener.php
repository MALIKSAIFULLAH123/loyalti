<?php

namespace MetaFox\Localize\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;

class ModelUpdatedListener
{
    public function handle(Model $model): void
    {
        $this->updateTranslatables($model);
    }

    protected function updateTranslatables(Model $model): void
    {
        if (!$model instanceof Entity) {
            return;
        }

        if (method_exists($model, 'updateTranslatables')) {
            $model->updateTranslatables();
        }
    }
}
