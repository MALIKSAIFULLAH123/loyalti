<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\User\Database\Factories\UserPrivacyTypeFactory;

/**
 * Class UserPrivacyType.
 * @property int    $id
 * @property string $name
 * @property int    $privacy_default
 * @mixin Builder
 */
class UserPrivacyType extends Model
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'user_privacy_type';

    protected $table = 'user_privacy_types';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'privacy_default',
    ];

    public static function factory(array $parameters = [])
    {
        return UserPrivacyTypeFactory::new($parameters);
    }
}

// end
