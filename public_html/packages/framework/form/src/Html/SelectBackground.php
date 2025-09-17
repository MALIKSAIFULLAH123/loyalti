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
class SelectBackground extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::SELECT_BACKGROUND)
            ->label(__p('core::phrase.background'));
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
