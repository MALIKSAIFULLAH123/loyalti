<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class Password.
 */
class Password extends Text
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::PASSWORD)
            ->name('password')
            ->setAttribute('type', 'password')
            ->autoComplete('password')
            ->maxLength(255)
            ->fullWidth(true)
            ->variant('outlined');
    }
}
