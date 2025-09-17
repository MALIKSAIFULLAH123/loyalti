<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Database\Factories\UserValueFactory;

/**
 * Class UserValue.
 *
 * @property int    $id
 * @property string $name
 * @property int    $value
 * @property int    $default_value
 * @property int    $ordering
 * @method static UserValueFactory factory(...$parameters)
 */
class UserValue extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'user_value';

    protected $table = 'user_values';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'name',
        'value',
        'default_value',
        'ordering',
    ];

    /**
     * @return UserValueFactory
     */
    protected static function newFactory(): UserValueFactory
    {
        return UserValueFactory::new();
    }
}

// end
