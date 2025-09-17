<?php

namespace MetaFox\Storage\Http\Resources\v1\Asset\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Storage\Models\Asset as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditAssetForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class EditAssetForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit_asset'))
            ->action(apiUrl('admin.storage.asset.upload', ['asset' => $this->resource->id]))
            ->asPost()
            ->asMultipart()
            ->setValue([
                'file' => [
                    'file_name' => basename($this->resource->url),
                    'file_type' => $this->resource->file_mime_type,
                    'url'       => $this->resource->url,
                ],
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::rawFile('file')
                    ->required()
                    ->allowPreview()
                    ->setAttribute('preventRemove', true)
                    ->placeholder(__p('core::phrase.upload'))
                    ->label(__p('core::phrase.title')),
            );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('core::phrase.save_changes'))
                    ->enableWhen([
                        'and',
                        ['eq', 'file.status', MetaFoxConstant::FILE_NEW_STATUS],
                    ]),
                Builder::cancelButton(),
            );
    }
}
