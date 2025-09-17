<?php

namespace MetaFox\Localize\Form\Html;

use MetaFox\Form\Html\Autocomplete;

/**
 * @driverType form-field
 * @driverName countryCity
 */
class SearchCountryCityCode extends Autocomplete
{
    public const COMPONENT = 'SearchCountryCityCode';

    public function initialize(): void
    {
        parent::initialize();

        $this->component(self::COMPONENT);
    }
}
