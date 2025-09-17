<?php

namespace MetaFox\Announcement\Observers;

use Illuminate\Support\Facades\Cache;
use MetaFox\Announcement\Models\Announcement as Model;
use MetaFox\Announcement\Support\CacheManager;

/**
 * Class AnnouncementObserver.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 */
class AnnouncementObserver
{
    /**
     * @param Model $model
     */
    public function created(Model $model): void
    {
        $this->clearCache();
    }

    /**
     * @param Model $model
     */
    public function updated(Model $model): void
    {
        $this->clearCache();
    }

    /**
     * @param Model $model
     */
    public function deleted(Model $model): void
    {
        $model->announcementText()->delete();
        $model->contents()->delete();
        $model->countries()->sync([]);
        $model->genders()->sync([]);

        $this->clearCache();
    }

    private function clearCache()
    {
        Cache::forget(CacheManager::SITE_ANNOUNCEMENT_CACHE);
    }
}

// end stub
