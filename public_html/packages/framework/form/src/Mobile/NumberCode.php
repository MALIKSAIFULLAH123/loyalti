<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Mobile;

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Form\Html\NumberCode as HtmlNumberCode;

/**
 * Class NumberCode.
 */
class NumberCode extends HtmlNumberCode
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::NUMBER_CODE);
    }
}
