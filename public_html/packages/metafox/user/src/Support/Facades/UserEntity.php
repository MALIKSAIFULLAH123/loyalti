<?php

namespace MetaFox\User\Support\Facades;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Facade;
use MetaFox\User\Models\UserEntity as Model;

/**
 * Class UserEntity.
 *
 * @method static Model      getById($entityId)
 * @method static int        createEntity(int $entityId, array $params)
 * @method static Model      updateEntity(int $entityId, array $params)
 * @method static int        deleteEntity(int $entityId)
 * @method static Collection getByIds(array $ids)
 * @method static int        forceDeleteEntity(int $entityId)
 * @see Model
 */
class UserEntity extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'UserEntity';
    }
}
