<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class Email.
 */
class Email extends Text
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::TEXT)
            ->autoComplete('email')
            ->maxLength(255)
            ->fullWidth(true)
            ->sizeMedium()
            ->marginNormal()
            ->variant('outlined');
    }
}
