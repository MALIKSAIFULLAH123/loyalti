<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class FilterCategoryField.
 */
class FilterCategoryField extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::FILTER_CATEGORY)
            ->name('category_id')
            ->label(__p('core::phrase.category'))
            ->fullWidth();
    }

    public function apiUrl(string $value): AbstractField|FilterCategoryField
    {
        return $this->setAttribute('apiUrl', $value);
    }
}
