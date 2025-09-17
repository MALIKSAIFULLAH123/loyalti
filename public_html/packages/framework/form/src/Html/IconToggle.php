<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class IconToggle.
 */
class IconToggle extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::ICON_TOGGLE)
            ->fullWidth(true)
            ->marginNormal()
            ->color('primary')
            ->labelPlacement('start');
    }

    public function color(string $color): self
    {
        return $this->setAttribute('color', $color);
    }

    public function icon(string $icon): self
    {
        return $this->setAttribute('icon', $icon);
    }

    public function componentLabel(string $value): self
    {
        return $this->setAttribute('componentLabel', $value);
    }

    public function tooltip(string $value): self
    {
        return $this->setAttribute('tooltip', $value);
    }
}
