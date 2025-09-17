<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\Constants;
use MetaFox\Yup\Yup;

class UrlField extends Text
{
    public function initialize(): void
    {
        $this->component(Constants::TEXT)
            ->label(__p('core::phrase.url'))
            ->placeholder(__p('core::phrase.url'))
            ->variant('outlined')
            ->marginNormal()
            ->sizeMedium()
            ->yup(
                Yup::string()->url()
            );
    }
}
