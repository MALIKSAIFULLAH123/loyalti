<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class Location.
 */
class MultipleLocation extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::MULTIPLE_LOCATION)
            ->fullWidth(true)
            ->variant('outlined')
            ->label(__p('core::phrase.location'))
            ->types(['locality']);
    }

    /**
     * Refer link: https://developers.google.com/maps/documentation/geocoding/requests-geocoding#Types.
     * @param  array $types
     * @return $this
     */
    public function types(array $types = []): static
    {
        return $this->setAttribute('types', $types);
    }
}
