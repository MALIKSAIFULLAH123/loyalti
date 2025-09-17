<?php

namespace MetaFox\Storage\Listeners;

use MetaFox\Platform\Contracts\Entity;

class ModelCreatedListener
{
    public function handle($model): void
    {
        if (!$model instanceof Entity) {
            return;
        }

        $fileColumns = $model->fileColumns ?? null;

        if (!is_array($fileColumns)) {
            return;
        }

        $values = [];

        foreach ($fileColumns as $name => $storage) {
            if (is_int($name)) {
                $name = $storage;
                $storage = null;
            }
            if ($model->{$name}) {
                $values[$model->{$name}] = $storage;
            }
        }

        if (empty($values)) {
            return;
        }

        app('storage')->attach($values, $model->entityId(), $model->entityType());
    }
}
