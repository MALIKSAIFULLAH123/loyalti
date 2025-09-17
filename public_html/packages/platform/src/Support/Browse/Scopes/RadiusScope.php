<?php

namespace MetaFox\Platform\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BoundsScope.
 */
class RadiusScope extends BaseScope
{
    protected ?float $lat = null;

    protected ?float $lng = null;

    protected ?float $radius = null;

    protected ?string $table = null;
    public const DEFAULT_MILES = 1;

    public const DEFAULT_LAT_FIELD = 'location_latitude';

    public const DEFAULT_LNG_FIELD = 'location_longitude';

    protected ?string $latField = null;

    protected ?string $lngField = null;

    /**
     * @param float|null $lat
     * @return $this
     */
    public function setLat(?float $lat): static
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getLat(): ?float
    {
        return $this->lat;
    }

    /**
     * @param float|null $lng
     * @return $this
     */
    public function setLng(?float $lng): static
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getLng(): ?float
    {
        return $this->lng;
    }

    /**
     * @param float|null $radius
     * @return $this
     */
    public function setRadius(?float $radius): static
    {
        $this->radius = $radius;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getRadius(): ?float
    {
        return $this->radius ?? self::DEFAULT_MILES;
    }

    /**
     * @param string $field
     * @return $this
     */
    public function setLatField(string $field): static
    {
        $this->latField = $field;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLatField(): ?string
    {
        return $this->latField ?? self::DEFAULT_LAT_FIELD;
    }

    /**
     * @param string $field
     * @return $this
     */
    public function setLngField(string $field): static
    {
        $this->lngField = $field;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLngField(): ?string
    {
        return $this->lngField ?? self::DEFAULT_LNG_FIELD;
    }

    /**
     * @return string|null
     */
    public function getTable(): ?string
    {
        return $this->table;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $lat = $this->getLat();

        $lng = $this->getLng();

        if (null === $lat) {
            return;
        }

        if (null === $lng) {
            return;
        }
        $radius = $this->getRadius();
        $table = $this->getTable() ?? $model->getTable();
        $latField = $this->alias($table, $this->getLatField());

        $lngField = $this->alias($table, $this->getLngField());

        $builder->whereRaw("
                       (3959 * acos(
                               cos( radians('{$lat}'))
                               * cos( radians( {$latField} ) )
                               * cos( radians( {$lngField} ) - radians('{$lng}') )
                               + sin( radians('{$lat}') ) * sin( radians( {$latField} ) )
                           )) < $radius
                         ");
    }
}
