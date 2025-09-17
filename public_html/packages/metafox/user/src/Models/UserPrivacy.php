<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class UserPrivacy.
 * @property int    $id
 * @property int    $user_id
 * @property int    $type_id
 * @property string $name
 * @property int    $privacy
 * @property int    $privacy_id
 * @mixin Builder
 */
class UserPrivacy extends Model
{
    use HasEntity;

    public const ENTITY_TYPE = 'user_privacy';

    protected $table = 'user_privacy_values';

    protected $fillable = [
        'user_id',
        'type_id',
        'name',
        'privacy',
        'privacy_id',
    ];

    public $timestamps = false;
}

// end
