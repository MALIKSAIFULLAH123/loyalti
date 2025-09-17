<?php

namespace MetaFox\Advertise\Support\Form\Mobile;

use MetaFox\Form\AbstractField;

class SponsorCalculatorCost extends AbstractField
{
    public const COMPONENT_NAME = 'SponsorCalculatorCost';

    public function initialize(): void
    {
        $currencyId = app('currency')->getUserCurrencyId(user());

        $pricePattern = app('currency')->getFormatForPrice($currencyId, null, true);

        $this->component(self::COMPONENT_NAME)
            ->totalNameLabel(__p('advertise::phrase.total_cost'))
            ->initialUnit(1000)
            ->pricePattern($pricePattern);
    }

    /**
     * @param  float $price
     * @return $this
     */
    public function initialPrice(float $price): static
    {
        return $this->setAttribute('initialPrice', $price);
    }

    /**
     * @param  int   $value
     * @return $this
     */
    public function initialUnit(int $value): static
    {
        return $this->setAttribute('initialUnit', $value);
    }

    /**
     * @param  string $name
     * @return $this
     */
    public function totalNameLabel(string $name): static
    {
        return $this->setAttribute('totalNameLabel', $name);
    }

    /**
     * @param  array|null $params
     * @return $this
     */
    public function pricePattern(?array $params): static
    {
        return $this->setAttribute('pricePattern', $params);
    }
}
