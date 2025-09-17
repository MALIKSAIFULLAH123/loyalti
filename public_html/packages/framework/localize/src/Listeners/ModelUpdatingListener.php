<?php

namespace MetaFox\Localize\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;

class ModelUpdatingListener
{
    public function handle(Model $model): void
    {
        $this->generateTranslatables($model);
    }

    protected function generateTranslatables(Model $model): void
    {
        if (!$model instanceof Entity) {
            return;
        }

        if (method_exists($model, 'updateTranslatableKeys')) {
            $model->updateTranslatableKeys();
        }
    }
}
