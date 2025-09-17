<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Form;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class SelectPackageAliasField.
 */
class SelectPackageAliasField extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::COMPONENT_SELECT)
            ->variant('outlined')
            ->fullWidth()
            ->options(resolve('core.packages')->getPackageOptions())
            ->label(__p('core::phrase.package_name'));
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

    /**
     * Setup for horizontal form.
     * @return $this
     */
    public function forAdminSearchForm(): static
    {
        return $this->sizeSmall()
            ->marginDense()
            ->maxWidth('220px');
    }
}
