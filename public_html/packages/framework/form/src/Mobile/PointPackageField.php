<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

class PointPackageField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent('PointPackage');
    }
}
