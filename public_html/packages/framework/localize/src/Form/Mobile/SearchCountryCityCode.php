<?php

namespace MetaFox\Localize\Form\Mobile;

use MetaFox\Form\Mobile\Autocomplete;

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

        $this->component(self::COMPONENT)
            ->variant('standard');
    }
}
