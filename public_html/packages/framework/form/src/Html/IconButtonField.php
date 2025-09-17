<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class IconButtonField.
 */
class IconButtonField extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::ICON_BUTTON_FIELD);
    }

    public function linkTo(string $linkTo): static
    {
        return $this->setAttribute('linkTo', $linkTo);
    }

    public function icon(string $icon): static
    {
        return $this->setAttribute('icon', $icon);
    }

    public function tooltip(string $tooltip): static
    {
        return $this->setAttribute('tooltip', $tooltip);
    }
}
