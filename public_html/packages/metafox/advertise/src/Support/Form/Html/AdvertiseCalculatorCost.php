<?php

namespace MetaFox\Advertise\Support\Form\Html;

use MetaFox\Form\AbstractField;

class AdvertiseCalculatorCost extends AbstractField
{
    public const COMPONENT_NAME = 'AdvertiseCalculatorCost';

    public function initialize(): void
    {
        $currencyId = app('currency')->getUserCurrencyId(user());

        $pricePattern = app('currency')->getFormatForPrice($currencyId, null, true);

        $this->component(self::COMPONENT_NAME)
            ->totalNameLabel(__p('advertise::phrase.total_cost'))
            ->initialUnit(1)
            ->pricePattern($pricePattern);
    }

    public function initialUnit(int $value): static
    {
        return $this->setAttribute('initialUnit', $value);
    }

    public function relatedInitialPrice(string $name): static
    {
        return $this->setAttribute('relatedInitialPrice', $name);
    }

    public function totalNameLabel(string $name): static
    {
        return $this->setAttribute('totalNameLabel', $name);
    }

    public function pricePattern(?array $params): static
    {
        return $this->setAttribute('pricePattern', $params);
    }

    public function placementOptions(array $options): static
    {
        return $this->setAttribute('placementOptions', $options);
    }
}
