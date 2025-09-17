<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\Constants as MetaFoxForm;

class PasswordField extends TextField
{
    public function initialize(): void
    {
        $this->setComponent(MetaFoxForm::PASSWORD)
            ->name('password')
            ->autoComplete('password')
            ->maxLength(255)
            ->returnKeyType('go')
            ->type('password')
            ->marginNone()
            ->setAttribute('paddingBottom', 'dense')
            ->variant('standard');
    }

    public function type(string $type): self
    {
        return $this->setAttribute('type', $type);
    }
}
