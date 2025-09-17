<?php

namespace MetaFox\Storage\Http\Resources\v1\Disk\Admin;

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
 * Class UpdateSftpForm.
 * @ignore
 * @codeCoverageIgnore
 */
class StoreDiskForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('storage::phrase.add_new_disk'))
            ->action('/admincp/storage/disk')
            ->asPost()
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('name')
                    ->required()
                    ->label(__p('storage::phrase.disk_name'))
                    ->description(__p('storage::phrase.disk_name_desc'))
                    ->yup(Yup::string()->required()),
                Builder::text('label')
                    ->required()
                    ->label(__p('storage::phrase.disk_label'))
                    ->description(__p('storage::phrase.disk_label_desc'))
                    ->yup(Yup::string()->required()),
                Builder::selectStorageId('disk')
                    ->required()
                    ->excludeDrivers(['alias'])
                    ->excludes(['local'])
                    ->label(__p('storage::phrase.disk_title'))
                    ->description(__p('storage::phrase.disk_title_desc'))
                    ->yup(Yup::string()->required()),
            );

        $this->addDefaultFooter(true);
    }
}
