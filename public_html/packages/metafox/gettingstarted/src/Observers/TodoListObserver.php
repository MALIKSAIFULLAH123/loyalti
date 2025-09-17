<?php

namespace MetaFox\GettingStarted\Observers;

use MetaFox\GettingStarted\Models\TodoList as Model;

/**
 * Class TodoListObserver.
 * @ignore
 * @codeCoverageIgnore
 */
class TodoListObserver
{
    public function deleted(Model $model): void
    {
        $model->descriptions()->delete();

        $model->images()->each(function ($data) {
            $data->delete();
        });
    }
}
