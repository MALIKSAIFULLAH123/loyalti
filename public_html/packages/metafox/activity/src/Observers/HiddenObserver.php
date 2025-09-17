<?php

namespace MetaFox\Activity\Observers;

use MetaFox\Activity\Contracts\ActivityHiddenManager;
use MetaFox\Activity\Models\Hidden;

/**
 * Class HiddenObserver.
 */
class HiddenObserver
{
    /** @var ActivityHiddenManager */
    private $hiddenManager;

    public function __construct(ActivityHiddenManager $hiddenManager)
    {
        $this->hiddenManager = $hiddenManager;
    }

    public function created(Hidden $model): void
    {
        $this->hiddenManager->clearCache($model->userId());
    }

    public function updated(Hidden $model): void
    {
        $this->hiddenManager->clearCache($model->userId());
    }

    public function deleted(Hidden $model): void
    {
        $this->hiddenManager->clearCache($model->userId());
    }
}

// end stub
