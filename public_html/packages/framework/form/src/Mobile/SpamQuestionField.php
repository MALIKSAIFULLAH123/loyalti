<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class SpamQuestionField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent('SpamQuestion');
    }
}
