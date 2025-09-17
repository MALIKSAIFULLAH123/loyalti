<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\Constants as MetaFoxForm;

class ComposerInput extends TextAreaField
{
    public function initialize(): void
    {
        $this->setComponent(MetaFoxForm::COMPOSER_INPUT)
            ->variant('standard')
            ->asMultiLine();
    }
}
