<?php

namespace MetaFox\Featured\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Featured\Database\Factories\PackageFactory;
use MetaFox\Platform\Contracts\User;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class Package
 *
 * @property int $id
 * @property string $title
 * @property array $price
 * @property string $duration_period
 * @property int $duration_value
 * @property bool $is_active
 * @property bool $is_free
 * @property int $total_end
 * @property int $total_cancelled
 * @property int $total_active
 * @property string $applicable_role_type
 * @property string $applicable_item_type
 * @property string|null $deleted_at
 * @property string $created_at
 * @property string $updated_at
 * @property string $duration_text
 * @property bool $is_forever_duration
 * @property Collection $item_types
 * @property Collection $role_ids
 * @method static PackageFactory factory(...$parameters)
 */
class Package extends Model implements Entity, HasTitle
{
    use HasEntity;
    use HasFactory;
    use SoftDeletes;
    use HasAmountsTrait;

    public const ENTITY_TYPE = 'featured_package';

    protected $table = 'featured_packages';

    /** @var string[] */
    protected $fillable = [
        'title',
        'price',
        'duration_period',
        'duration_value',
        'is_active',
        'is_free',
        'is_forever_duration',
        'total_end',
        'total_cancelled',
        'total_active',
        'applicable_role_type',
        'applicable_item_type',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public $casts = [
        'price' => 'array',
        'is_active' => 'boolean',
        'is_free'   => 'boolean',
        'is_forever_duration' => 'boolean',
        'total_cancelled' => 'integer',
        'total_end' => 'integer',
        'total_active' => 'integer',
    ];

    /**
     * @return PackageFactory
     */
    protected static function newFactory()
    {
        return PackageFactory::new();
    }

    public function toTitle(): string
    {
        return $this->title ?? MetaFoxConstant::EMPTY_STRING;
    }

    public function getDurationTextAttribute(): string
    {
        return Feature::getDurationText($this->duration_period, $this->duration_value);
    }

    public function getItemTypesAttribute(): HasMany
    {
        return $this->hasMany(ApplicableItemType::class, 'package_id', 'id');
    }

    public function getRoleIdsAttribute(): HasMany
    {
        return $this->hasMany(ApplicableRole::class, 'package_id', 'id');
    }

    public function getPriceByCurrency(string $currency): ?float
    {
        return LoadReduce::remember(sprintf('featured::package::getPriceByCurrency(%s,%s)', $this->entityId(), $currency), function () use ($currency) {
            if ($this->is_free) {
                return 0;
            }

            $price = $this->price;

            if (!is_array($price)) {
                return null;
            }

            $price = Arr::get($price, $currency);

            if (!is_numeric($price)) {
                return null;
            }

            return $price;
        });
    }

    public function getPriceForUser(User $user): ?float
    {
        return LoadReduce::remember(sprintf('featured::package::getPriceForUser(%s,%s)', $this->entityId(), $user->entityId()), function () use ($user) {
            if ($this->is_free) {
                return 0;
            }

            $price = $this->price;

            if (!is_array($price)) {
                return null;
            }

            $userCurrencyId = app('currency')->getUserCurrencyId($user);

            $price = Arr::get($price, $userCurrencyId);

            if (!is_numeric($price)) {
                return null;
            }

            return $price;
        });
    }
}

// end
