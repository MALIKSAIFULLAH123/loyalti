<?php

namespace MetaFox\Page\Support\Facade;

use Illuminate\Support\Facades\Facade;
use MetaFox\Page\Contracts\PageClaimContract;

/**
 * @method static array      getAllowStatusOptions()
 * @method static array      getAllowStatus()
 * @method static array      getAllowStatusId()
 * @method static int        getStatusId(string $key)
 * @method static array|null getStatusInfo(string $status)
 */
class PageClaim extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PageClaimContract::class;
    }
}
