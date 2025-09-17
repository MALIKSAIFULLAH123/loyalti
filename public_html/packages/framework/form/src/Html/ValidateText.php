<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class ValidateText.
 */
class ValidateText extends Text
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::VALIDATE_TEXT)
            ->autoComplete('email')
            ->maxLength(255)
            ->fullWidth()
            ->sizeMedium()
            ->marginNormal()
            ->variant('outlined');
    }

    public function validateAction(string $action): self
    {
        return $this->setAttribute('validateAction', $action);
    }
}
