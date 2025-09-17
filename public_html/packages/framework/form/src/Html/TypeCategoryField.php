<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class TypeCategoryField.
 */
class TypeCategoryField extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::FIELD_TYPE_CATEGORY)
            ->setValue('outlined')
            ->fullWidth(true);
    }

    /**
     * @param  array<int, mixed> $data
     * @return $this
     */
    public function options(array $data): self
    {
        return $this->setAttribute('options', $data);
    }
}
