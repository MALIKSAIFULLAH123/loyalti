<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\User\Database\Factories\MultiFactorTokenFactory;

/**
 * Class MultiFactorToken.
 *
 * @property int    $id
 * @method   static MultiFactorTokenFactory factory(...$parameters)
 */
class MultiFactorToken extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = '';

    protected $table = 'user_multi_factor_token';

    /** @var string[] */
    protected $fillable = [
        'email',
        'hash_code',
        'updated_at',
        'created_at',
    ];

    /**
     * @return MultiFactorTokenFactory
     */
    protected static function newFactory()
    {
        return MultiFactorTokenFactory::new();
    }
}

// end
