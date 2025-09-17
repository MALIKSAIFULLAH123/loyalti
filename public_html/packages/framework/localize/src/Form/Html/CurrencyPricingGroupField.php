<?php

namespace MetaFox\Localize\Form\Html;

use MetaFox\Form\Builder;
use MetaFox\Form\Section;
use MetaFox\Yup\Yup;

/**
 * @driverName currencyPricingGroup
 * @driverType form-field
 */
class CurrencyPricingGroupField extends Section
{
    public function buildFields(?string $name = null): self
    {
        $codes = collect(app('currency')->getAllActiveCurrencies())->keys()->values()->toArray();

        $name  = $name ?? $this->getName();

        foreach ($codes as $code) {
            $this->addField(
                Builder::text(sprintf('%s.%s', $name, $code))
                    ->label($code)
                    ->required($this->getAttribute('required', true))
                    ->startAdornment($code)
                    ->sx(['width' => 250, 'mr' => 2])
                    ->minWidth(250)
                    ->fullWidth(false)
                    ->preventScrolling()
                    ->asNumber()
                    ->yup(Yup::number()->positive()->required(__p('core::validation.this_field_is_a_required_field')))
            );
        }

        return $this;
    }
}
