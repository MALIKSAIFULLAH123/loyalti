<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class CheckboxField.
 */
class CheckboxField extends AbstractField
{
    public function initialize(): void
    {
        $this->setAttributes([
            'component'      => MetaFoxForm::CHECKBOX_FIELD,
            'checkedValue'   => 1,
            'uncheckedValue' => 0,
            'fullWidth'      => true,
            'margin'         => 'dense',
        ]);
    }

    /**
     * Set checkedValue=true, and uncheckedValue=false.
     * @return $this
     */
    public function asBoolean(): self
    {
        return $this->setAttribute('checkedValue', true)
            ->setAttribute('uncheckedValue', false);
    }

    public function checkedValue(mixed $flag): self
    {
        return $this->setAttribute('checkedValue', $flag);
    }

    public function uncheckedValue(mixed $flag): self
    {
        return $this->setAttribute('uncheckedValue', $flag);
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
}
