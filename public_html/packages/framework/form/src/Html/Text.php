<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class Text.
 * @phpstan-consistent-constructo
 */
class Text extends AbstractField
{
    public function initialize(): void
    {
        $this->name('text')
            ->maxLength(255)
            ->fullWidth(true)
            ->variant('outlined')
            ->marginNormal()
            ->sizeMedium()
            ->component(MetaFoxForm::TEXT);
    }

    public function hasFormLabel(bool $flag = true): AbstractField|Text
    {
        return $this->setAttribute('hasFormLabel', $flag);
    }

    /**
     * One of primary, secondary, danger, info.
     *
     * @param  string  $color
     *
     * @return $this
     */
    public function color(string $color): self
    {
        return $this->setAttribute('color', $color);
    }

    public function asNumber(): self
    {
        return $this->setAttribute('type', 'number');
    }

    public function multipleLine(): self
    {
        return $this->setAttribute('multiline', true);
    }

    public function rows(int $rows): self
    {
        return $this->setAttribute('rows', $rows);
    }

    public function preventScrolling(): self
    {
        return $this->setAttribute('preventScrolling', true);
    }

    public function forAdminSearchForm(): static
    {
        return $this
            ->label(__p('core::phrase.search_dot'))
            ->optional()
            ->sizeSmall()
            ->marginDense()
            ->maxWidth('220px');
    }

    public function forceLabelShrink()
    {
        return $this->setAttribute('InputLabelProps', ['shrink' => true]);
    }
}
