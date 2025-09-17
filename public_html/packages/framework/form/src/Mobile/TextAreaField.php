<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

class TextAreaField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent(MetaFoxForm::TEXT_AREA)
            ->variant('standard')
            ->asMultiLine();
    }

    public function asMultiLine(bool $flag = true): self
    {
        return $this->setAttribute('multipleline', $flag);
    }

    public function textAlignVertical(string $position): self
    {
        return $this->setAttribute('textAlignVertical', $position);
    }
}
