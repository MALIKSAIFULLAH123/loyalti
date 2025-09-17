<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class SearchTextBoxField.
 */
class SearchTextBoxField extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::SEARCH_TEXT_BOX_FIELD);
    }
}
