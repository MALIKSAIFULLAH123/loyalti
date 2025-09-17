<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class Autocomplete.
 */
class Autocomplete extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::AUTOCOMPLETE)
            ->marginNormal()
            ->sizeMedium()
            ->maxLength(255)
            ->variant('outlined');
    }

    /**
     * @param  string $endpoint
     * @return $this
     */
    public function searchEndpoint(string $endpoint): self
    {
        return $this->setAttribute('search_endpoint', $endpoint);
    }

    /**
     * @param  array $params
     * @return $this
     */
    public function searchParams(array $params): self
    {
        return $this->setAttribute('search_params', $params);
    }
}
