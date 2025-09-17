<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * @deprecated
 * Class SingleUpdateInputField.
 */
class SingleUpdateInputField extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::SINGLE_UPDATE_INPUT)
            ->fullWidth(true)
            ->variant('outlined')
            ->editComponent('select')
            ->options([]);
    }

    /**
     * @param string $component
     *
     * @return self
     */
    public function editComponent(string $component): self
    {
        return $this->setAttribute('editComponent', $component);
    }

    public function options(array $options): self
    {
        return $this->setAttribute('options', $options);
    }
}
