<?php

namespace MetaFox\Platform\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LocationScope.
 */
class LocationScope extends BaseScope
{
    public const DEFAULT_COUNTRY_FIELD = 'country_iso';

    protected ?string $country = null;

    protected ?string $stateField = null;
    protected ?string $state      = null;

    protected ?string $countryField = null;
    protected ?string $city         = null;

    protected ?string $cityField = null;

    protected ?string $table = null;

    /**
     * @return string|null
     */
    public function getStateField(): ?string
    {
        return $this->stateField;
    }

    /**
     * @param string|null $stateField
     */
    public function setStateField(?string $stateField): void
    {
        $this->stateField = $stateField;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     */
    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string|null
     */
    public function getCityField(): ?string
    {
        return $this->cityField;
    }

    /**
     * @param string|null $cityField
     */
    public function setCityField(?string $cityField): void
    {
        $this->cityField = $cityField;
    }

    /**
     * @param string|null $country
     *
     * @return $this
     */
    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $field
     *
     * @return $this
     */
    public function setCountryField(?string $field): static
    {
        $this->countryField = $field;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountryField(): ?string
    {
        return $this->countryField ?? self::DEFAULT_COUNTRY_FIELD;
    }

    /**
     * @param string $table
     *
     * @return $this
     */
    public function setTable(string $table): static
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTable(): ?string
    {
        return $this->table;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $table = $this->getTable() ?? $model->getTable();

        $builder->where(function ($builder) use ($table) {
            $this->buildCountryCondition($builder, $table);
            $this->buildStateCondition($builder, $table);
            $this->buildCityCondition($builder, $table);
        });
    }

    protected function buildCountryCondition(Builder $builder, string $table)
    {
        $country = $this->getCountry();

        if ($country) {
            $builder->where($this->alias($table, $this->getCountryField()), $country);
        }
    }

    protected function buildStateCondition(Builder $builder, string $table)
    {
        $state = $this->getState();

        if ($state) {
            $builder->where($this->alias($table, $this->getStateField()), $state);
        }
    }

    protected function buildCityCondition(Builder $builder, string $table)
    {
        $city = $this->getCity();

        if ($city) {
            $builder->where($this->alias($table, $this->getCityField()), $city);
        }
    }
}
