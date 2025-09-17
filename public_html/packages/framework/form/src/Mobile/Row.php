<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\Section;

/**
 * @driverType form-field-mobile
 * @driverName row
 */
class Row extends Section
{
    public const COMPONENT = 'Row';

    public function initialize(): void
    {
        $this->setComponent(self::COMPONENT);
    }
}
