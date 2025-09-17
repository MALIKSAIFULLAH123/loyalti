<?php

namespace MetaFox\Localize\Form\Html;

use MetaFox\Form\Html\Autocomplete;

/**
 * @driverType form-field
 * @driverName countryCity
 */
class CountryCityCode extends Autocomplete
{
    public const COMPONENT = 'CountryCityCode';

    public function initialize(): void
    {
        parent::initialize();
        $this->component(self::COMPONENT);
    }
}
