<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

class Autocomplete extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent(MetaFoxForm::AUTOCOMPLETE)
            ->maxLength(255)
            ->variant('standard');
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
     * @param  array<string, mixed> $params
     * @return $this
     */
    public function searchParams(array $params): self
    {
        return $this->setAttribute('search_params', $params);
    }

    /**
     * @param  string $key
     * @return $this
     */
    public function valueKey(string $key): self
    {
        return $this->setAttribute('valueKey', $key);
    }

    /**
     * @param  string $key
     * @return $this
     */
    public function labelKey(string $key): self
    {
        return $this->setAttribute('labelKey', $key);
    }
}
