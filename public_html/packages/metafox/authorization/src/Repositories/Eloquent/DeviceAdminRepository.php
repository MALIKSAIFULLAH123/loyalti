<?php

namespace MetaFox\Authorization\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use MetaFox\Authorization\Repositories\DeviceAdminRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Authorization\Models\UserDevice;

/**
 * Class DeviceRepository.
 * @method UserDevice getModel()
 * @method UserDevice find($id, $columns = ['*'])
 */
class DeviceAdminRepository extends AbstractRepository implements DeviceAdminRepositoryInterface
{
    public function model(): string
    {
        return UserDevice::class;
    }

    /**
     * @inheritDoc
     */
    public function updateDevice(User $context, int $id, array $attributes = []): UserDevice
    {
        $device = $this->find($id);
        $device->update($attributes);

        return $device->refresh();
    }

    /**
     * @inheritDoc
     */
    public function viewDevices(User $context, array $attributes = []): Paginator
    {
        $limit = Arr::get($attributes, 'limit');

        return $this->getModel()
            ->newModelQuery()
            ->paginate($limit);
    }

    /**
     * @inheritDoc
     */
    public function deleteDevice(User $context, int $id): bool
    {
        $device = $this->find($id);

        return (bool) $device->delete();
    }
}
