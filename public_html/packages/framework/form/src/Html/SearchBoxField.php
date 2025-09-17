<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class SearchBoxField.
 */
class SearchBoxField extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::SEARCH_BOX_FIELD)
            ->name('q')
            ->placeholder(__p('localize::phrase.search_dot'));
    }
}
