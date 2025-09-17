<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class Language.
 */
class Language extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::COMPONENT_SELECT)
            ->fullWidth(true)
            ->variant('outlined')
            ->options([]);
    }

    public function options(array $options): static
    {
        return $this->setAttribute('options', $options);
    }
}
