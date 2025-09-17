<?php

namespace MetaFox\Localize\Form\Html;

use MetaFox\Form\Html\Autocomplete;

class SelectPhrase extends Autocomplete
{
    public function initialize(): void
    {
        parent::initialize();
        $this->searchEndpoint('/admincp/phrase/suggest');
    }
}
