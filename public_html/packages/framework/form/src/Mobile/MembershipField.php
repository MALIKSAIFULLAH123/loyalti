<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class MembershipField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent('Membership');
    }
}
