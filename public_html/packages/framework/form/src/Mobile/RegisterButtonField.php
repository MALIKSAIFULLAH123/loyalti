<?php

namespace MetaFox\Form\Mobile;

/**
 * Class LinkButtonField.
 *
 * @driverType form-field-mobile
 * @driverName registerButton
 */
class RegisterButtonField extends LinkButtonField
{
    public const COMPONENT = 'RegisterButton';

    public function initialize(): void
    {
        parent::initialize();

        $this->setComponent(self::COMPONENT);
    }
}
