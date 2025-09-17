<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class TagsField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent('Tags')
            ->description(__p('core::phrase.separate_multiple_topics_with_enter'));
    }
}
