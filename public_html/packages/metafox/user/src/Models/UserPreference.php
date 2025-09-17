<?php

namespace MetaFox\User\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class UserPreference.
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $user_type
 * @property string $name
 * @property string $type
 * @property mixed  $value
 * @property string $created_at
 * @property string $updated_at
 */
class UserPreference extends Model implements Entity
{
    use HasEntity;
    use HasUserMorph;

    public const ENTITY_TYPE = 'user_preference';

    protected $table = 'user_preferences';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'name',
        'type',
        'value',
        'created_at',
        'updated_at',
    ];

    public function getValueAttribute(mixed $value): mixed
    {
        return match ($this->type) {
            'integer', 'int' => (int) $value,
            'boolean', 'bool' => (bool) ((int) $value),
            'array' => is_string($value) ? json_decode($value, true) : $value,
            default => $value ? (string) $value : $value,
        };
    }

    public function getAdminBrowseUrlAttribute(): string
    {
        return '';
    }

    public function getAdminEditUrlAttribute(): string
    {
        return '';
    }
}

// end
