<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class FriendPicker.
 */
class FriendPicker extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::FRIEND_PICKER);
    }

    public function endpoint(?string $endpoint): static
    {
        return $this->setAttribute('api_endpoint', $endpoint);
    }

    public function resetWhenUnmount(bool $value = true): static
    {
        return $this->setAttribute('resetWhenUnmount', $value);
    }
}
