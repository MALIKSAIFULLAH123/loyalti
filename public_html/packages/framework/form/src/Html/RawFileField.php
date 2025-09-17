<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class RawFileField.
 */
class RawFileField extends File
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::RAW_FILE)
            ->name('file')
            ->required()
            ->multiple(false)
            ->maxUploadSize(3000)
            ->placeholder(__p('core::phrase.attachment'));
    }

    public function maxUploadSize(int $maxUploadSize): self
    {
        return $this->setAttribute('maxUploadSize', $maxUploadSize);
    }

    public function accept(string $accept): self
    {
        return $this->setAttribute('accept', $accept);
    }

    public function allowPreview(): self
    {
        return $this->setAttribute('showPreview', true);
    }

    public function preventRemove(): self
    {
        return $this->setAttribute('preventRemove', true);
    }
}
