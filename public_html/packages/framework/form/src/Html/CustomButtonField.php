<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class CancelButton.
 */
class CustomButtonField extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::CUSTOM_BUTTON)
            ->name('_custom_button')
            ->variant('outlined')
            ->fullWidth(false);
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

    public function customAction(array $value): self
    {
        return $this->setAttribute('customAction', $value);
    }
}
