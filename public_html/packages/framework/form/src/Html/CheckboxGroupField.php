<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class CheckboxGroupField.
 */
class CheckboxGroupField extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::CHECKBOX_GROUP)
            ->fullWidth(true)
            ->options([]);
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function options(array $options): self
    {
        return $this->setAttribute('options', $options);
    }

    /**
     * One of primary, secondary, danger, info.
     *
     * @param string $color
     *
     * @return $this
     */
    public function color(string $color): self
    {
        return $this->setAttribute('color', $color);
    }

    public function enableCheckAll(): self
    {
        return $this->setAttribute('selectAllToggle', true);
    }
}
