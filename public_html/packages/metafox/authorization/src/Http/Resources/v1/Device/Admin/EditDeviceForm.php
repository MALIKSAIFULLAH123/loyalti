<?php

namespace MetaFox\Authorization\Http\Resources\v1\Device\Admin;

use MetaFox\Authorization\Repositories\DeviceRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\Authorization\Models\UserDevice as Model;

/**
 * Class EditDeviceForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class EditDeviceForm extends AbstractForm
{
    public function boot(DeviceRepositoryInterface $repository, ?int $device = null): void
    {
        $this->resource = $repository->find($device);
    }

    protected function prepare(): void
    {
        $this->title(__p('authorization::phrase.edit_device'))
            ->action(apiUrl('admin.authorization.device.update', ['device' => $this->resource->entityId()]))
            ->asPut()
            ->setValue([
                'token'     => $this->resource->device_token,
                'device_id' => $this->resource->device_id,
                'platform'  => $this->resource->platform,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('token')
                    ->required()
                    ->label(__p('authorization::phrase.device_token'))
                    ->yup(Yup::string()->required()),
                Builder::text('device_id')
                    ->required()
                    ->label(__p('authorization::phrase.device_id'))
                    ->yup(Yup::string()->required()),
                Builder::text('platform')
                    ->required()
                    ->label(__p('authorization::phrase.device_platform'))
                    ->yup(Yup::string()->required()),
            );

        $this->addDefaultFooter();
    }
}
