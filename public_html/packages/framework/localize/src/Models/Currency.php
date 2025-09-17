<?php

namespace MetaFox\Localize\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MetaFox\Localize\Database\Factories\CurrencyFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\User\Models\UserProfile;

/**
 * Class Currency.
 *
 * @property int    $id
 * @property string $code
 * @property string $symbol
 * @property string $name
 * @property string $format
 * @property int    $is_active
 * @property int    $is_default
 * @property int    $ordering
 */
class Currency extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'currency';

    protected $table = 'core_currencies';

    public const IS_ACTIVE  = 1;
    public const IS_DEFAULT = 1;

    protected $fillable = [
        'code',
        'symbol',
        'name',
        'format',
        'is_active',
        'is_default',
        'ordering',
    ];

    protected $perPage = 500;

    public $timestamps = false;

    /** @var array<string, string> */
    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    /**
     * @return CurrencyFactory
     */
    protected static function newFactory()
    {
        return CurrencyFactory::new();
    }

    public function users(): HasMany
    {
        return $this->hasMany(UserProfile::class, 'currency_id', 'code');
    }

    public function getIsUsingAttribute(): bool
    {
        return $this->users()->exists();
    }

    public function isActive(): ?bool
    {
        if ($this->is_default || $this->is_using) {
            return null;
        }

        return $this->is_active;
    }
}
