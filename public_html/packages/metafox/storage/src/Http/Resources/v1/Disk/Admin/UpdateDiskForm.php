<?php

namespace MetaFox\Storage\Http\Resources\v1\Disk\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Storage\Http\Requests\v1\Disk\Admin\UpdateAliasDiskRequest as Request;
use MetaFox\Storage\Models\Disk;
use MetaFox\Storage\Support\StorageDiskValidator;
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
 * @property Disk $resource
 */
class UpdateDiskForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('storage::phrase.edit_disk'))
            ->action('/admincp/storage/disk/' . $this->resource->id)
            ->asPut()
            ->setValue($this->resource->toArray());
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('name')
                    ->required()
                    ->label(__p('storage::phrase.disk_name'))
                    ->description(__p('storage::phrase.disk_name_desc'))
                    ->disabled()
                    ->yup(Yup::string()->required()),
                Builder::text('label')
                    ->required()
                    ->label(__p('storage::phrase.disk_label'))
                    ->description(__p('storage::phrase.disk_label_desc'))
                    ->yup(Yup::string()->required()),
                Builder::selectStorageId('target')
                    ->required()
                    ->excludeDrivers(['alias'])
                    ->excludes(['local'])
                    ->label(__p('storage::phrase.disk_title'))
                    ->description(__p('storage::phrase.disk_title_desc'))
                    ->yup(Yup::string()->required()),
            );

        $this->addDefaultFooter(true);
    }

    /**
     * @param  Request      $request
     * @return array<mixed>
     */
    public function validated(Request $request): array
    {
        $data = $request->validated();

        StorageDiskValidator::isValid($data);

        return $data;
    }
}
