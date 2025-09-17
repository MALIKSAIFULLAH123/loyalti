<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Form;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class SelectPackageField.
 */
class SelectPackageField extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::COMPONENT_SELECT)
            ->variant('outlined')
            ->fullWidth()
            ->options(resolve('core.packages')->getPackageIdOptions())
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

    public function forAdminSearchForm(): static
    {
        return $this->sizeSmall()
            ->required(false)
            ->fullWidth(false)
            ->margin('dense')
            ->sizeSmall()
            ->minWidth('200px');
    }
}
