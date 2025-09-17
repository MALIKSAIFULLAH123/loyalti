<?php

namespace MetaFox\Payment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Payment\Contracts\GatewayInterface;
use MetaFox\Payment\Database\Factories\GatewayFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class Gateway.
 *
 * @property int        $id
 * @property string     $service
 * @property int        $is_active
 * @property int        $is_test
 * @property string     $title
 * @property string     $description
 * @property array      $config
 * @property string     $service_class
 * @property string     $filter_mode
 * @property array      $filter_list
 * @property Collection $filters
 * @property string     $icon
 * @property string     $module_id
 * @property bool       $enable_buyer_config
 * @property bool       $enable_seller_config
 */
class Gateway extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'gateway';

    public const IMPORTER_ENTITY_TYPE = 'payment_gateway';

    protected $table = 'payment_gateway';

    public const IS_ACTIVE           = 1;
    public const IS_TEST             = 1;
    public const DEFAULT_FILTER_MODE = 'blacklist';

    protected $fillable = [
        'service',
        'is_active',
        'is_test',
        'title',
        'description',
        'config',
        'service_class',
        'module_id',
        'enable_buyer_config',
        'enable_seller_config',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'config'  => 'array',
        'is_test' => 'bool',
        'enable_buyer_config'  => 'bool',
        'enable_seller_config' => 'bool',
    ];

    /**
     * @var string[]
     */
    protected $appends = [
        'filter_mode',
        'filter_list',
        'icon',
    ];

    protected $perPage = 500;

    public $timestamps = false;

    /**
     * @return GatewayFactory
     */
    protected static function newFactory()
    {
        return GatewayFactory::new();
    }

    public function getService(): GatewayInterface
    {
        /** @var ?GatewayInterface $service */
        $service = resolve($this->service_class, ['gateway' => $this]);

        if (!$service instanceof GatewayInterface) {
            throw new ServiceNotFoundException($this->service);
        }

        return $service;
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'gateway_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'gateway_id');
    }

    public function filters(): BelongsToMany
    {
        return $this->belongsToMany(
            GatewayFilter::class,
            'payment_gateway_filter_data',
            'gateway_id',
            'filter_id'
        )->using(FilterData::class);
    }

    public function getFilterModeAttribute(): string
    {
        return Arr::get($this->config, 'filter_mode', static::DEFAULT_FILTER_MODE);
    }

    /**
     * @return array<string>|null
     */
    public function getFilterListAttribute(): ?array
    {
        if (empty($this->filters)) {
            return null;
        }

        return $this->filters->map(function (GatewayFilter $filter) {
            return $filter->entity_type;
        })->values()->toArray();
    }

    public function getIconAttribute(): string
    {
        return Arr::get($this->config, 'icon', '') ?? '';
    }
}
