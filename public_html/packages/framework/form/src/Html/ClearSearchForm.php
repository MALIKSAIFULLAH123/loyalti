<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class ClearSearchForm.
 */
class ClearSearchForm extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::COMPONENT_CLEAR_SEARCH_FORM);
    }

    public function excludeFields(array $exclude): self
    {
        return $this->setAttribute('excludeFields', $exclude);
    }

    public function align(string $align): self
    {
        return $this->setAttribute('align', $align);
    }
}
