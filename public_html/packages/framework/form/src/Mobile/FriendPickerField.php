<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class FriendPickerField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent('FriendPicker');
    }

    public function endpoint(?string $endpoint): static
    {
        return $this->setAttribute('api_endpoint', $endpoint);
    }

    /**
     * @param  array<string, mixed> $params
     * @return $this
     */
    public function params(array $params = []): self
    {
        return $this->setAttribute('api_params', $params);
    }
}
