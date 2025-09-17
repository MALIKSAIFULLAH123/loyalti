<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\Constants as MetaFoxForm;

/**
 * @driverType form-field-mobile
 */
class EmailField extends TextField
{
    public function initialize(): void
    {
        $this->setComponent(MetaFoxForm::EMAIL)
            ->autoComplete('email')
            ->sizeMedium()
            ->marginNormal()
            ->returnKeyType('next')
            ->keyboardType('email-address')
            ->autoCapitalize('none')
            ->clearButtonMode('while-editing')
            ->textContentType('username')
            ->maxLength(255)
            ->variant('standard');
    }

    public function paddingBottom(string $padding): self
    {
        return $this->setAttribute('paddingBottom', $padding);
    }
}
