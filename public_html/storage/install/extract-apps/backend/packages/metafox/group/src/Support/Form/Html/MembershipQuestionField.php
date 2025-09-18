<?php

namespace MetaFox\Group\Support\Form\Html;

use MetaFox\Form\AbstractField;

class MembershipQuestionField extends AbstractField
{
    public function initialize(): void
    {
        $this->component('MembershipQuestion');
    }
}
