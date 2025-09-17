<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\PrivacyOptionsTrait;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class Privacy.
 */
class Privacy extends AbstractField
{
    use PrivacyOptionsTrait;

    public function initialize(): void
    {
        $this->component(MetaFoxForm::PRIVACY)
            ->label(__p('core::phrase.privacy'))
            ->name('privacy')
            ->fullWidth(true)
            ->variant('outlined')
            ->options($this->getFieldPrivacyOptions());
    }

    /**
     * assign current value.
     */
    protected function prepare(): void
    {
        $name     = $this->getName();
        $resource = $this->form?->getResource();

        if (!$resource) {
            return;
        }

        if ($name
            && !$this->form?->hasValue($name)
            && $resource instanceof HasPrivacy
        ) {
            $this->form?->assignValue($name, $resource->privacy);
        }
    }

    /**
     * @param array<array<string,mixed>> $options
     *
     * @return $this
     */
    public function options(array $options): self
    {
        return $this->setAttribute('options', $options);
    }
}
