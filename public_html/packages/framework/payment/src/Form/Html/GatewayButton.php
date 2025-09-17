<?php

namespace MetaFox\Payment\Form\Html;

use MetaFox\Form\AbstractField;

class GatewayButton extends AbstractField
{
    public const COMPONENT = 'GatewayButton';

    public function initialize(): void
    {
        $this->setComponent(self::COMPONENT);
    }
}
