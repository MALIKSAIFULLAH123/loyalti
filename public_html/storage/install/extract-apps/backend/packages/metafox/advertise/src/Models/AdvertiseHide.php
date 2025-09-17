<?php

namespace MetaFox\Advertise\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Advertise\Database\Factories\AdvertiseHideFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class AdvertiseHide.
 *
 * @property        int                  $id
 * @method   static AdvertiseHideFactory factory(...$parameters)
 */
class AdvertiseHide extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'advertise_hide';

    protected $table = 'advertise_hides';

    /** @var string[] */
    protected $fillable = [
        'item_id',
        'item_type',
        'user_id',
        'user_type',
    ];

    /**
     * @return AdvertiseHideFactory
     */
    protected static function newFactory()
    {
        return AdvertiseHideFactory::new();
    }
}

// end
