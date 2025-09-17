<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\Constants;

class PhoneNumberField extends TextField
{
    public function initialize(): void
    {
        $this->component(Constants::TEXT)
            ->variant('standardInlined')
            ->label(__p('core::phrase.phone_number'))
            ->placeholder(__p('core::phrase.phone_number'))
            ->validateAction('user.user.validatePhoneNumber');
    }
}
