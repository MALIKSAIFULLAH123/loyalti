<?php

namespace MetaFox\Localize\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;

class ModelCreatedListener
{
    public function handle(Model $model): void
    {
        $this->createTranslatables($model);
    }

    protected function createTranslatables(Model $model): void
    {
        if (!$model instanceof Entity) {
            return;
        }

        if (method_exists($model, 'createTranslatables')) {
            $model->createTranslatables();
        }
    }
}
