<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\Constants as MetaFoxForm;

class PhoneNumberField extends ValidateText
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::VALIDATE_TEXT)
            ->label(__p('core::phrase.phone_number'))
            ->placeholder(__p('core::phrase.phone_number'))
            ->validateAction('user.user.validatePhoneNumber');
    }
}
