<?php

namespace MetaFox\Advertise\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use MetaFox\Advertise\Support\Facades\Support as Facade;
use MetaFox\Advertise\Support\Support;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Advertise\Database\Factories\PlacementFactory;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Placement.
 *
 * @property int    $id
 * @property string $title
 * @property array  $price
 * @property array  $allowed_user_roles
 * @property
 * @method static PlacementFactory factory(...$parameters)
 */
class Placement extends Model implements
    Entity,
    HasTitle
{
    use HasEntity;
    use HasFactory;
    use SoftDeletes;
    use HasNestedAttributes;

    public const ENTITY_TYPE = 'advertise_placement';

    protected $table = 'advertise_placements';

    /** @var string[] */
    protected $fillable = [
        'title',
        'price',
        'allowed_user_roles',
        'is_active',
        'placement_type',
    ];

    /**
     * @var array<string>|array<string, mixed>
     */
    public array $nestedAttributes = [
        'placementText' => ['text', 'text_parsed'],
    ];

    protected $casts = [
        'price'              => 'array',
        'is_active'          => 'boolean',
        'allowed_user_roles' => 'array',
    ];

    /**
     * @return PlacementFactory
     */
    protected static function newFactory()
    {
        return PlacementFactory::new();
    }

    public function toTitle(): string
    {
        return Arr::get($this->attributes, 'title', MetaFoxConstant::EMPTY_STRING);
    }

    public function invoices(): HasManyThrough
    {
        return $this->hasManyThrough(Invoice::class, Advertise::class, 'placement_id', 'item_id')
            ->where('advertise_invoices.item_type', '=', Advertise::ENTITY_TYPE)
            ->where('advertise_invoices.payment_status', '<>', Facade::getPendingActionStatus());
    }

    public function advertises(): HasMany
    {
        return $this->hasMany(Advertise::class, 'placement_id');
    }

    public function getAllowedUserRolesAttribute(): ?array
    {
        if (!Arr::has($this->attributes, 'allowed_user_roles')) {
            return [];
        }

        $selectedRoles = Arr::get($this->attributes, 'allowed_user_roles');

        if (null === $selectedRoles) {
            return null;
        }

        if (is_string($selectedRoles)) {
            $selectedRoles = json_decode($selectedRoles, true);
        }

        return $selectedRoles;
    }

    public function getPlacementTypeTextAttribute(): ?string
    {
        $type = Arr::get($this->attributes, 'placement_type');

        return match ($type) {
            Support::PLACEMENT_CPM => __p('advertise::phrase.cpm'),
            Support::PLACEMENT_PPC => __p('advertise::phrase.ppc'),
            default                => null,
        };
    }

    public function placementText(): HasOne
    {
        return $this->hasOne(PlacementText::class, 'id');
    }

    public function toAdmincpAdsLink(): string
    {
        return url_utility()->makeApiUrl('advertise/advertise/browse?placement_id=' . $this->entityId());
    }

    public function isFree(string $currencyId): bool
    {
        $prices = $this->price;

        if (!is_array($prices)) {
            return false;
        }

        $price = Arr::get($prices, $currencyId);

        if (null === $price) {
            return false;
        }

        if ((float) $price == 0) {
            return true;
        }

        return false;
    }

    public function getPriceAttribute(): array
    {
        $prices = $this->getAttributeFromArray('price');

        if (is_string($prices)) {
            $prices = json_decode($prices, true);
        }

        if (!is_array($prices) || !count($prices)) {
            return [];
        }

        foreach ($prices as $key => $price) {
            if (!is_numeric($price)) {
                continue;
            }

            $prices[$key] = round($price, 2);
        }

        return $prices;
    }
}
