<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class MentionInputField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent('MentionField');
    }
}
