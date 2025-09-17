<?php

namespace MetaFox\Localize\Form\Mobile;

use MetaFox\Form\Mobile\Autocomplete;

/**
 * @driverType form-field
 * @driverName countryStatePicker
 */
class CountryStatePicker extends Autocomplete
{
    public const COMPONENT = 'countryStatePicker';

    public function initialize(): void
    {
        parent::initialize();
        $this->component(self::COMPONENT)
            ->variant('standard');
    }
}
