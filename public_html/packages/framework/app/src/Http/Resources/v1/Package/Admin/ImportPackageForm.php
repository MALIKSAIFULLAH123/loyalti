<?php

namespace MetaFox\App\Http\Resources\v1\Package\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class ImportModuleForm.
 */
class ImportPackageForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title('Import App')
            ->action('/admincp/app/package/import')
            ->asPost()
            ->asMultipart();
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::rawFile('file')
                ->accepts('.zip')
                ->maxUploadSize(20000000)
                ->label('Attach package')
                ->placeholder('Attach package'),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('core::phrase.upload')),
                Builder::cancelButton(),
            );
    }
}
