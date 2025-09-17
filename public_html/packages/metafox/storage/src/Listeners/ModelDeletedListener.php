<?php

namespace MetaFox\Storage\Listeners;

use MetaFox\Platform\Contracts\Entity;

class ModelDeletedListener
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

        foreach ($fileColumns as $name => $storage) {
            if (is_int($name)) {
                $name    = $storage;
                $storage = null;
            }
            if ($model->{$name}) {
                app('storage')->rollDown($model->{$name});
            }
        }
    }
}
