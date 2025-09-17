<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants;

class Button extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent(Constants::BUTTON);
    }

    public function forBottomSheetForm(?string $name = null, bool $optionContext = false): static
    {
        return $this->setComponent($name ?? 'SFFilterForm');
    }
}
