<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class Submit.
 */
class Submit extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::SUBMIT)
            ->name('_submit')
            ->sizeMedium()
            ->variant('contained')
            ->label(__p('core::phrase.save_changes'))
            ->type('submit')
            ->color('primary');
    }

    public function type(string $type): self
    {
        return $this->setAttribute('type', $type);
    }

    public function color(string $color): self
    {
        return $this->setAttribute('color', $color);
    }

    public function flexWidth(int|bool $value): static
    {
        return $this->setAttribute('flexWidth', $value);
    }

    public function disableWhenClean(bool $value = true): static
    {
        return $this->setAttribute('disableWhenClean', $value);
    }

    public function forAdminSearchForm(): static
    {
        return $this->srOnly()
            ->label(__p('core::phrase.search'))
            ->marginDense();
    }
}
