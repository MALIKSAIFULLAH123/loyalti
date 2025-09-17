<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class AddTextStyle.
 */
class AddTextStyle extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::ADD_TEXT_STYLE)
            ->label(__p('core::phrase.add_text'));
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

    public function options(array $options): self
    {
        return $this->setAttribute('options', $options);
    }
}
