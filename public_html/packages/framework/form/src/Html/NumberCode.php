<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class NumberCode.
 */
class NumberCode extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::NUMBER_CODE);
    }
}
