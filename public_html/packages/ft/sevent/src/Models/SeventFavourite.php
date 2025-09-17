<?php

namespace Foxexpert\Sevent\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use Foxexpert\Sevent\Database\Factories\SeventFavouriteFactory;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class SeventFavourite
 *
 * @property int $id
 * @method static SeventFavouriteFactory factory(...$parameters)
 */
class SeventFavourite extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'Favourite';

    protected $table = 'sevent_favourite';

    /** @var string[] */
    protected $fillable = [
        'owner_id',
        'owner_type',
        'sevent_id'
    ];

    /**
     * @return SeventFavouriteFactory
     */
    protected static function newFactory()
    {
        return SeventFavouriteFactory::new();
    }
}

// end
