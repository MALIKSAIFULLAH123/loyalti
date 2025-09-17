<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Form;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class YesNoChoice
 * Support YesNoChoice.
 */
class YesNoChoice extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::COMPONENT_SELECT)
            ->options([
                ['label' => __p('core::phrase.yes'), 'value' => 1],
                ['label' => __p('core::phrase.no'), 'value' => 0],
            ])
            ->fullWidth(true);
    }

    /**
     * Setup for horizontal form.
     * @return $this
     */
    public function forAdminSearchForm(): static
    {
        return $this->maxWidth('220px')
            ->sizeSmall()
            ->marginDense();
    }

    /**
     * @param  array<array<string,mixed>  $options
     *
     * @return $this
     */
    public function options(array $options): self
    {
        return $this->setAttribute('options', $options);
    }
}
