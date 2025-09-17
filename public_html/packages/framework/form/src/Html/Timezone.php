<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class Timezone.
 */
class Timezone extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::CHOICE)
            ->variant('outlined')
            ->name('timezone')
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
}
