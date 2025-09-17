<?php

namespace MetaFox\Session\Support;

use MetaFox\Form\Html\CheckboxField;

class CheckDefaultSessionDriverField extends CheckboxField
{
    protected function prepare(): void
    {
        $this->name('is_default')
            ->label(__p('session::phrase.driver_label'))
            ->description(__p('session::phrase.driver_desc'));
    }
}
