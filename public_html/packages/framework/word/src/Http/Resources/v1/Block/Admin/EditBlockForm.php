<?php

namespace MetaFox\Word\Http\Resources\v1\Block\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Word\Models\Block as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreBlockForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class EditBlockForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('word::block.edit_word'))
            ->action(apiUrl('admin.word.block.update', ['block' => $this->resource?->id]))
            ->asPut()
            ->setValue(['word' => $this->resource?->word]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('word')
                    ->required()
                    ->label(__p('word::block.word_label'))
                    ->description(__p('word::block.word_desc'))
                    ->maxLength(64)
                    ->yup(Yup::string()->maxLength(64)->required()),
            );

        $this->addDefaultFooter(true);
    }
}
