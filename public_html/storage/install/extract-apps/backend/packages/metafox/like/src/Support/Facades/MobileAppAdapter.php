<?php

namespace MetaFox\Like\Support\Facades;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Facade;
use MetaFox\Like\Support\MobileAppAdapter as MobileAppAdapterSupport;

/**
 * Class Invite.
 *
 * @method static int toCompatibleData($id, $version)
 * @method static int transformLegacyData($id, $version)
 * @method static Collection getReactionsForConfig()
 * @see \MetaFox\Contact\Support\Contact
 */
class MobileAppAdapter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MobileAppAdapterSupport::class;
    }
}
