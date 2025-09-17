<?php

namespace MetaFox\Storage\Http\Resources\v1\Admin;

use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Storage\Support\SelectUniqueDiskId;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreDiskForm.
 * @ignore
 * @codeCoverageIgnore
 * @driverName: storage.disk.store
 */
class SelectDiskDriverForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('storage::phrase.add_new_config'))
            ->action(apiUrl('admin.storage.option.store'))
            ->asPost()
            ->setValue(['driver' => 's3']);
    }

    private function getDriverOptions(): array
    {
        $drivers = resolve(DriverRepositoryInterface::class)
            ->getDrivers('form-storage', null, 'admin');

        $options = [];
        foreach ($drivers as $driver) {
            if ($driver->name === 'alias') {
                continue;
            }

            if (!$driver->is_active) {
                continue;
            }

            $options[] = ['value' => $driver->name, 'label' => $driver->title];
        }

        return $options;
    }

    protected function initialize(): void
    {
        $driverOptions = $this->getDriverOptions();
        $this->addBasic()
            ->addFields(
                new SelectUniqueDiskId(['name' => 'id']),
                Builder::choice('driver')
                    ->required()
                    ->options($driverOptions)
                    ->label(__p('storage::phrase.select_disk_driver'))
                    ->yup(Yup::string()->required()),
            );

        $this->addFooter()
            ->addFields(
                Builder::cancelButton(),
                Builder::submit()
                    ->label(__p('core::phrase.continue')),
            );
    }
}
