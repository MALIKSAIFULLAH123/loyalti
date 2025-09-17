<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use MetaFox\Localize\Support\Traits\HasTranslatableAttributes;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class UserGender.
 *
 * @mixin Builder
 *
 * @property int    $id
 * @property string $phrase
 * @property string $name
 * @property bool   $is_custom
 * @property bool   $is_system
 * @property string $created_at
 * @property string $updated_at
 */
class UserGender extends Model implements Entity
{
    use HasEntity;
    use HasTranslatableAttributes;

    public const ENTITY_TYPE = 'user_gender';

    protected $table = 'user_gender';

    /** @var string[] */
    protected $fillable = [
        'phrase',
        'name',
        'is_custom',
        'updated_at',
    ];

    protected $casts = [
        'is_custom' => 'boolean',
    ];

    protected $translatableAttributes = [
        'phrase',
    ];

    public function getNameAttribute(): string
    {
        return __p($this->phrase);
    }

    public function getIsSystemAttribute(): bool
    {
        return !$this->is_custom;
    }
}

// end
