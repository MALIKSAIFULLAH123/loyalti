<?php

namespace MetaFox\Importer\Http\Resources\v1\Bundle\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Importer\Models\Bundle as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateBundleForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateBundleForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->action(apiUrl('admin.importer.bundle.store'))
            ->description(__p('importer::phrase.form_upload_guide'))
            ->asMultipart()
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::rawFile('file')
                    ->accepts('.zip')
                    ->required()
                    ->maxUploadSize(2000000000)
                    ->label('Attach archive file')
                    ->placeholder(__p('importer::phrase.attach_file')),
                Builder::choice('chat_type')
                    ->label(__p('importer::phrase.select_chat_app_to_import'))
                    ->required()
                ->options([
                    [
                        'value' => 'chat',
                        'label' => 'Chat',
                    ],
                    [
                        'value' => 'chatplus',
                        'label' => 'ChatPlus',
                    ],

                ]),
                Builder::switch('keep_item_id')
                    ->label(__p('importer::phrase.keep_item_id'))
                    ->description(__p('importer::phrase.keep_item_id_desc'))
            );

        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__p('importer::phrase.upload')),
                Builder::cancelButton(),
            );
    }
}
