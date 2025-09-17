<?php

namespace MetaFox\Localize\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;

class ModelCreatingListener
{
    public function handle(Model $model): void
    {
        $this->generateTranslatableAttributes($model);
    }

    protected function generateTranslatableAttributes(Model $model): void
    {
        if (!$model instanceof Entity) {
            return;
        }

        if (method_exists($model, 'generateTranslatableKeys')) {
            $model->generateTranslatableKeys();
        }
    }
}
