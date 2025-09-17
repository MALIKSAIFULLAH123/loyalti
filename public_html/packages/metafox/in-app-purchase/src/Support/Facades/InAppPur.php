<?php

namespace MetaFox\InAppPurchase\Support\Facades;

/*
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

use Illuminate\Support\Facades\Facade;
use MetaFox\InAppPurchase\Support\InAppPurchase;
use MetaFox\Platform\Contracts\User;

/**
 * Class InAppPur.
 * @method static array getProductTypes(bool $toForm = true);
 * @method static array|null getProductTypeByValue(string $value);
 * @method static bool handleCallback(string $platform, array $data);
 * @method static array getSettingFormFields();
 * @method static bool verifyReceipt(array $data, User $context);
 * @see InAppPurchase
 **/
class InAppPur extends Facade
{
    protected static function getFacadeAccessor()
    {
        return InAppPurchase::class;
    }
}
