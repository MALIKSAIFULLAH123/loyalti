<?php

namespace MetaFox\Localize\Http\Resources\v1\Language\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Localize\Models\Phrase as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class ImportPhraseForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UploadCSVForm extends AbstractForm
{
    protected function prepare(): void
    {
        $name = $this->resource?->name;

        $this->title(__p('localize::phrase.import_phrases') . ($name ? " ($name)" : ''))
            ->action(apiUrl('admin.localize.language.uploadCSVFile', ['id' => $this->resource->id]))
            ->asMultipart();
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::rawFile('file')
                    ->required()
                    ->placeholder(__p('localize::phrase.upload_phrase_csv_file'))
                    ->accept('text/csv'),
            );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('core::web.upload')),
                Builder::cancelButton(),
            );
    }
}
