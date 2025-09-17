<?php

namespace MetaFox\ActivityPoint\Support\Facade;

use Illuminate\Support\Facades\Facade;
use MetaFox\ActivityPoint\Contracts\Support\ActionType as ActionTypeContract;
use MetaFox\ActivityPoint\Support\ActionType as ActionTypeModel;
use MetaFox\Platform\Contracts\Entity;

/**
 * @method static ActionTypeModel|null getActionType(Entity $resource, string $action)
 * @method static void                 setupDefaultActionTypes(?string $packageId = null)
 * @method static void                 setupCustomActionTypes(?string $packageId = null)
 * @method static void                 setupActionTypesInterpolateFromTransaction(?string $packageId = null)
 * @method static array                createActionTypesData(string $packageId, mixed $resource, array $actions)
 * @method static void                 migrateTransactionExistPointSetting(?string $packageId =  null)
 * @method static void                 migrateTransactionNotExistPointSetting(?string $packageId = null)
 * @method static array                getActionTypeOptions()
 */
class ActionType extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ActionTypeContract::class;
    }
}
