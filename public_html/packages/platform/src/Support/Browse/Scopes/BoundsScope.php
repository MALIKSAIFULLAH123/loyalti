<?php

namespace MetaFox\Platform\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BoundsScope.
 */
class BoundsScope extends BaseScope
{
    protected ?array $bounds       = null;
    protected ?string $table       = null;
    public const DEFAULT_LAT_FIELD = 'location_latitude';
    public const DEFAULT_LNG_FIELD = 'location_longitude';

    /**
     * @param  array|null $bounds
     * @return $this
     */
    public function setBounds(?array $bounds): static
    {
        $this->bounds = $bounds;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getBounds(): array
    {
        return $this->bounds ?? [];
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
        $table  = $this->getTable();
        $bounds = array_filter($this->getBounds());
        if (empty($bounds)) {
            return;
        }

        $lngField = self::DEFAULT_LNG_FIELD;
        $latField = self::DEFAULT_LAT_FIELD;
        $south    = $bounds['south'];
        $north    = $bounds['north'];
        $west     = $bounds['west'];
        $east     = $bounds['east'];

        if ($table == null) {
            $table = $model->getTable();
        }

        $builder->where($this->alias($table, $latField), '!=', 0)
            ->where($this->alias($table, $lngField), '!=', 0);

        if ($south && $north) {
            $builder->where($this->alias($table, $latField), '>=', $south)
                ->where($this->alias($table, $latField), '<=', $north);
        }

        if ($west && $east) {
            if ($west < $east) {
                $builder->where($this->alias($table, $lngField), '>=', $west)
                    ->where($this->alias($table, $lngField), '<=', $east);
            } else {
                $builder->where($this->alias($table, $lngField), '<=', $west)
                    ->where($this->alias($table, $lngField), '>=', $east);
            }
        }
    }
}
