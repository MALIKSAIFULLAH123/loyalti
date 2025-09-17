<?php

namespace MetaFox\Authorization\Observers;

use Illuminate\Support\Facades\Cache;
use MetaFox\Authorization\Models\Role;
use MetaFox\User\Support\CacheManager;

/**
 * Class UserRoleObserver.
 */
class UserRoleObserver
{
    public function created(Role $userRole): void
    {
        $this->clearCache();
    }

    public function updated(Role $userRole): void
    {
        $this->clearCache();
    }

    public function deleted(Role $userRole): void
    {
        $this->clearCache();
    }

    protected function clearCache(): void
    {
        Cache::forget(CacheManager::AUTH_ROLE_OPTIONS_CACHE);
    }
}

// end stub
