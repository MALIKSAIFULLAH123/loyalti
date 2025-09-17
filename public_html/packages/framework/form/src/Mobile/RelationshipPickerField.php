<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class RelationshipPickerField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent('RelationshipPicker');
    }
}
