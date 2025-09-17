<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class Date.
 */
class Slug extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::COMPONENT_SLUG);
    }

    /**
     * @param  string $field
     * @return $this
     */
    public function mappingField(string $field): self
    {
        return $this->setAttribute('mappingField', $field);
    }

    public function separator(string $value): self
    {
        return $this->setAttribute('separator', $value);
    }
}
