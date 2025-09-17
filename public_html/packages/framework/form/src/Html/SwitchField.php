<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class SwitchField.
 */
class SwitchField extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::SWITCH_FIELD)
            ->fullWidth(true)
            ->marginNormal()
            ->color('primary')
            ->labelPlacement('start');
    }

    public function color(string $color): self
    {
        return $this->setAttribute('color', $color);
    }
}
