<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class CustomField.
 */
class CustomField extends Radio
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::COMPONENT_CUSTOM_FIELD)
            ->fullWidth();
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

    /**
     * @param array<array<string,mixed> $options
     *
     * @return $this
     */
    public function options(array $options): self
    {
        return $this->setAttribute('options', $options);
    }

    /**
     * @param  array $options
     * @return $this
     */
    public function allowOptions(array $options): self
    {
        return $this->setAttribute('allowOptions', $options);
    }
}
