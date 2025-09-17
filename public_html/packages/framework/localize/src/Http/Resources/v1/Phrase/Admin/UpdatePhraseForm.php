<?php

namespace MetaFox\Localize\Http\Resources\v1\Phrase\Admin;

use MetaFox\Core\Support\Facades\Language;
use MetaFox\Form\Builder;
use MetaFox\Form\AbstractForm;
use MetaFox\Localize\Models\Phrase as Model;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdatePhraseForm.
 * @property Model $resource
 */
class UpdatePhraseForm extends AbstractForm
{
    public function boot(?int $id = null): void
    {
        $this->resource = resolve(PhraseRepositoryInterface::class)->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit_phrase'))
            ->action('admincp/phrase/' . $this->resource->id)
            ->asPut()
            ->setValue([
                'key'  => $this->resource->key,
                'text' => $this->resource->text,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::textArea('text')
                ->variant('outlined')
                ->required()
                ->description(__p('localize::phrase.text_value_desc', ['key' => $this->resource->key, 'default_text' => $this->resource->default_text]))
                ->label(__p('localize::phrase.text_value'))
                ->placeholder(__p('localize::phrase.text_value')),
        );

        $this->addDefaultFooter(true);
    }
}
