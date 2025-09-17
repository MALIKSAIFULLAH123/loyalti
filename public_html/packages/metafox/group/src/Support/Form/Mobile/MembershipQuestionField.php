<?php

namespace MetaFox\Group\Support\Form\Mobile;

use MetaFox\Form\AbstractField;

class MembershipQuestionField extends AbstractField
{
    public function initialize(): void
    {
        $this->component('MembershipQuestion');
    }
}
