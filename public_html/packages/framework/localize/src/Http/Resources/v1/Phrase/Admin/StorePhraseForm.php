<?php

namespace MetaFox\Localize\Http\Resources\v1\Phrase\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Localize\Models\Phrase as Model;

/**
 * Class StorePhraseForm.
 * @property Model $resource
 */
class StorePhraseForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->asPost()
            ->title(__p('core::phrase.add_new_phrase'))
            ->action('admincp/phrase')
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::translatableText('name')
                ->required()
                ->label(__p('localize::phrase.text_value'))
                ->buildFields(),
        );

        $this->addDefaultFooter();
    }
}
