<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\Constants as MetaFoxForm;

class SearchSuggestionField extends Choice
{
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);

        $this->setAttribute('apiMethod', MetaFoxForm::METHOD_GET);
    }

    /**
     * @param  array $params
     * @return $this
     */
    public function dependencyParams(array $params): self
    {
        return $this->setAttribute('dependencyParams', $params);
    }

    /**
     * @param  string $endpoint
     * @return $this
     */
    public function apiUrl(string $endpoint): self
    {
        return $this->setAttribute('apiUrl', $endpoint);
    }

    /**
     * @param  array $params
     * @return $this
     */
    public function apiParams(array $params): self
    {
        return $this->setAttribute('apiParams', $params);
    }
}
