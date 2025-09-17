<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class TextArea.
 */
class TextArea extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::TEXT_AREA)
            ->fullWidth(true)
            ->variant('outlined');
    }

    public function cols(int $number): self
    {
        return $this->setAttribute('cols', $number);
    }

    public function rows(int $number): self
    {
        return $this->setAttribute('rows', $number);
    }

    /**
     * @param  mixed $value
     * @return void
     * @deprecated
     */
    public function editor(mixed $value)
    {
        // nothing.
    }

    /**
     * Disable editor mode.
     * @return $this
     * @deprecated
     */
    public function disableEditor(): self
    {
        return $this->setAttribute('editor', false);
    }

    public function forAdminSearchForm(): static
    {
        return $this
            ->component(MetaFoxForm::TEXT)
            ->label(__p('core::phrase.search_dot'))
            ->optional()
            ->sizeSmall()
            ->marginDense()
            ->maxWidth('220px');
    }
}
