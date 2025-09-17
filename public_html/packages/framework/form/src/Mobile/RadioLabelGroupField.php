<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

class RadioLabelGroupField extends RadioGroupField
{
    public const COMPONENT = 'RadioLabelGroup';

    public function initialize(): void
    {
        parent::initialize();

        $this->setComponent(self::COMPONENT);
    }
}
