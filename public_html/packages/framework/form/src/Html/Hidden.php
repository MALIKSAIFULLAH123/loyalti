<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;

/**
 * Class Hidden.
 *
 * Configuration input type="hidden" form field.
 */
class Hidden extends AbstractField
{
    public function initialize(): void
    {
        $this->component('Hidden');
    }
}
