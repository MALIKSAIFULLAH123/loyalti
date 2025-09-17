<?php

namespace MetaFox\Localize\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;

class ModelDeletedListener
{
    public function handle(Model $model): void
    {
        $this->deleteTranslatableAttributes($model);
    }

    protected function deleteTranslatableAttributes(Model $model): void
    {
        if (!$model instanceof Entity) {
            return;
        }

        if (method_exists($model, 'deleteTranslatables')) {
            $model->deleteTranslatables();
        }
    }
}
