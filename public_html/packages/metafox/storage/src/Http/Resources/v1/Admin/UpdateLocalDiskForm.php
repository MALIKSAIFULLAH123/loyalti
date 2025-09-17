<?php

namespace MetaFox\Storage\Http\Resources\v1\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Storage\Http\Requests\v1\Disk\Admin\UpdateLocalDiskRequest as Request;
use MetaFox\Storage\Support\SelectDiskVisibility;
use MetaFox\Storage\Support\StorageDiskValidator;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateLocalDiskForm.
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateLocalDiskForm extends AbstractForm
{
    protected function prepare(): void
    {
        $resource = $this->resource;
        $value    = $resource['value'] ?? [];
        $value    = array_merge(['visibility' => 'public', 'driver' => 'local'], $value);
        $action   = apiUrl('admin.storage.option.update', ['driver' => $resource['driver'], 'disk' => $resource['id']]);

        $this->title(__p('storage::phrase.update_local_title'))
            ->action($action)
            ->asPut()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('root')
                    ->required()
                    ->label(__p('storage::phrase.local_root'))
                    ->yup(Yup::string()->required()),
                Builder::text('url')
                    ->required()
                    ->label(__p('storage::phrase.local_url'))
                    ->yup(Yup::string()->format('url')),
                new SelectDiskVisibility(),
                Builder::hidden('driver'),
                Builder::checkbox('throw')
                    ->required()
                    ->label(__p('storage::phrase.storage_throws')),
            );

        $this->addDefaultFooter(true);
    }

    /**
     * @param  Request $request
     * @return array
     */
    public function validated(Request $request): array
    {
        $data = $request->validated();

        StorageDiskValidator::isValid($data);

        return $data;
    }
}
