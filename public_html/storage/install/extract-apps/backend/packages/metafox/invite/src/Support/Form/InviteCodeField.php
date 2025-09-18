<?php

namespace MetaFox\Invite\Support\Form;

use MetaFox\Form\AbstractField;

class InviteCodeField extends AbstractField
{
    public function initialize(): void
    {
        $this->component("InviteCode")
            ->marginNormal()
            ->sizeMedium()
            ->variant('outlined');
    }
}
