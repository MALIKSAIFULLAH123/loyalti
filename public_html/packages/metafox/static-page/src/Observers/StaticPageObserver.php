<?php

namespace MetaFox\StaticPage\Observers;

use MetaFox\StaticPage\Models\StaticPage as Model;

/**
 * Class StaticPageObserver.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 */
class StaticPageObserver
{
    /**
     * @param Model $model
     */
    public function deleted(Model $model): void
    {
        $model->contents()->delete();
    }
}

// end stub
