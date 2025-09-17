<?php

namespace MetaFox\Event\Support\Browse\Scopes\Event;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 */
class WhereScope extends BaseScope
{
    /**
     * @var string
     */
    private ?string $country;

    /**
     * Get the value of country.
     *
     * @return ?string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set the value of country.
     *
     * @param string $country
     *
     * @return self
     */
    public function setCountry(?string $country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $country = $this->getCountry();

        if ($country) {
            $builder->where('country_iso', $country);
        }
    }
}
