<?php

namespace MetaFox\GettingStarted\Observers;

use MetaFox\GettingStarted\Models\TodoListImage as Model;

/**
 * Class TodoListImageObserver.
 * @ignore
 * @codeCoverageIgnore
 */
class TodoListImageObserver
{
    public function deleted(Model $model): void
    {
        app('storage')->rollDown($model->image_file_id);
    }
}
