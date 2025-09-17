<?php

namespace MetaFox\Firebase\Http\Resources\v1\Admin;

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
 * Class ImportSettingForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class ImportSettingForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->action(apiUrl('admin.firebase.setting.store'))
            ->description(__p('firebase::phrase.import_settings_desc'))
            ->asMultipart()
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::rawFile('file')
                    ->accepts('.json')
                    ->required()
                    ->maxUploadSize(2000000000)
                    ->placeholder(__p('firebase::phrase.json_file')),
            );

        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__p('core::phrase.upload')),
                Builder::cancelButton(),
            );
    }
}
