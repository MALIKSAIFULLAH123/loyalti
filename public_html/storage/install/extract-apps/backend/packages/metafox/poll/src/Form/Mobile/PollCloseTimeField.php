<?php

namespace MetaFox\Poll\Form\Mobile;

use MetaFox\Form\Mobile\DateField;

/**
 * @driverName pollCloseTime
 * @driverType form-field-mobile
 */
class PollCloseTimeField extends DateField
{
    public const COMPONENT = 'PollCloseTime';

    public function initialize(): void
    {
        parent::initialize();

        $this->setComponent(self::COMPONENT)
            ->variant('standard')
            ->datePickerMode('datetime');
    }
}
