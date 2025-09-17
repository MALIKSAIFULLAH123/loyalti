<?php

namespace MetaFox\Form;

/**
 * @property array $resource
 */
class Confirm extends AbstractForm
{
    protected function initialize(): void
    {
        $this->title($this->resource['title'] ?? __p('core::phrase.confirm'));

        $this->addFooter()->addFields(
            Builder::submit()->label(__p('core::web.ok')),
            Builder::cancelButton()->label(__p('core::phrase.cancel'))
        );
    }
}
