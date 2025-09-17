<?php

namespace MetaFox\HealthCheck\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\HealthCheck\Contracts\NoticeManager as ContractsNoticeManager;

/**
 * class NoticeManager.
 * @method static array collectReports()
 * @see MetaFox\HealthCheck\Support\NoticeManager
 */
class NoticeManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ContractsNoticeManager::class;
    }
}
