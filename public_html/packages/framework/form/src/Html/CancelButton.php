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
class CancelButton extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::CANCEL_BUTTON)
            ->name('_cancel')
            ->color('primary')
            ->variant('outlined')
            ->fullWidth(false)
            ->label(__p('core::phrase.cancel'));
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
