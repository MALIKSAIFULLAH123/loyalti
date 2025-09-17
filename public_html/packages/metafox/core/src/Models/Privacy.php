<?php

namespace MetaFox\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class Privacy.
 *
 * @property int    $privacy
 * @property int    $privacy_id
 * @property int    $item_id
 * @property int    $user_id
 * @property int    $owner_id
 * @property string $item_type
 * @property string $user_type
 * @property string $owner_type
 * @property string $privacy_type
 * @mixin Builder
 */
class Privacy extends Model implements Entity
{
    use HasEntity;
    public const ENTITY_TYPE = 'core_privacy';

    protected $table = 'core_privacy';

    protected $primaryKey = 'privacy_id';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'item_type',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'privacy_type',
        'privacy',
    ];
}
