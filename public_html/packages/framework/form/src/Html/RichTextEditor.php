<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class Editor.
 */
class RichTextEditor extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::RICH_TEXT_EDITOR)
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
     * Ensure `emptyValue` supports both empty string and null when necessary.
     * Released in core version v5.17
     * @param string|null $value
     * @return self
     */
    public function emptyValue(?string $value = ''): self
    {
        return $this->setAttribute('emptyValue', $value);
    }
}
