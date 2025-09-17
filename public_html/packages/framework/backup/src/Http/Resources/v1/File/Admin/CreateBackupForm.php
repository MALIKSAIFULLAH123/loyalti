<?php

namespace MetaFox\Backup\Http\Resources\v1\File\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateBackupForm.
 * @ignore
 * @codeCoverageIgnore
 */
class CreateBackupForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->action(apiUrl('admin.backup.file.store'))
            ->asPost()
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::typography('intro')
                    ->required()
                    ->plainText(__p('backup::phrase.create_backup_guide'))
                    ->yup(Yup::string()),
            );

        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__p('backup::phrase.backup')),
                Builder::cancelButton()
            );
    }
}
