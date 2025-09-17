<?php

namespace MetaFox\Authorization\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Laravel\Passport\Token;
use MetaFox\Authorization\Models\UserDevice;
use MetaFox\Authorization\Repositories\DeviceRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Traits\Helpers\InputCleanerTrait;

/**
 * Class DeviceRepository.
 * @method UserDevice getModel()
 * @method UserDevice find($id, $columns = ['*'])
 */
class DeviceRepository extends AbstractRepository implements DeviceRepositoryInterface
{
    use InputCleanerTrait;

    public function model(): string
    {
        return UserDevice::class;
    }

    /**
     * @inheritDoc
     */
    public function updateOrCreateDevice(User $context, array $attributes = []): UserDevice
    {
        $deviceUID = Arr::get($attributes, 'device_uid');
        $token     = Arr::get($attributes, 'device_token');

        $devices = $this->getDevices($context, [
            'device_uid'   => $deviceUID,
            'device_token' => $token,
        ]);
        if ($devices->isEmpty()) {
            return $this->createDevice($context, $attributes);
        }

        // Update the token and device with new user.
        // or
        // Create new one
        /** @var UserDevice $newDevice */
        $newDevice = $this->getModel()
            ->newModelQuery()
            ->firstOrNew([
                'device_uid'   => $deviceUID,
                'device_token' => $token,
            ], array_merge($attributes, [
                'user_id'   => $context->entityId(),
                'user_type' => $context->entityType(),
            ]));

        $newDevice->save();

        return $newDevice->refresh();
    }

    /**
     * @inheritDoc
     */
    public function deleteDeviceById(User $context, string $deviceId): ?array
    {
        $devices = $this->getModel()->newModelQuery()
            ->where('user_id', '=', $context->entityId())
            ->where('device_uid', '=', $deviceId)
            ->get()
            ->collect();

        if ($devices->isEmpty()) {
            return null;
        }

        $tokens = $devices->pluck('device_token')->toArray();

        $devices->each(function (UserDevice $device) {
            $device->delete();
        });

        return $tokens;
    }

    /**
     * @inheritDoc
     */
    public function getDevices(User $context, array $attributes = []): Collection
    {
        $deviceUID = Arr::get($attributes, 'device_uid');
        $token     = Arr::get($attributes, 'device_token');

        $query = $this->getModel()
            ->newModelQuery()
            ->where('user_id', '=', $context->entityId());

        if ($deviceUID) {
            $query->where('device_uid', '=', $deviceUID);
        }

        if ($token) {
            $query->where('device_token', '=', $token);
        }

        return $query
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->collect();
    }

    /**
     * @inheritDoc
     */
    public function getUserActiveTokens(User $context, ?string $platform = null): array
    {
        $query = $this->getQueryDevicesByUser($context);

        if ($platform) {
            $query = $query->where('platform', '=', $platform);
        }

        return $query
            ->get(['device_token'])
            ->pluck('device_token')
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function createDevice(User $context, array $attributes = []): UserDevice
    {
        $device = new UserDevice();
        $device->fill(array_merge($attributes, [
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
            'is_active' => 1,
        ]));

        $device->save();

        return $device;
    }

    /**
     * @param  User        $context
     * @param  string|null $tokenId
     * @return void
     */
    public function logoutAllByUser(User $context, ?string $tokenId = null): void
    {
        if (!$context instanceof \MetaFox\User\Models\User) {
            return;
        }

        $tokens     = $this->getDeviceTokensByUser($context, $tokenId);

        $this->inactiveDevicesByUser($context, $tokenId);

        $contextTokens = $context->tokens();

        if ($tokenId) {
            $contextTokens?->whereNot('id', $tokenId);
        }

        $contextTokens?->each(function (?Token $item) {
            $item?->revoke();
        });

        if (!empty($tokens)) {
            app('firebase.fcm')->removeUserDeviceGroup($context->entityId(), $tokens);
        }
    }

    public function inactiveDevicesByUser(User $context, ?string $tokenId): void
    {
        $query = $this->getQueryDevicesByUser($context);

        if ($tokenId) {
            $query->whereNot('token_id', '=', $tokenId);
        }

        $query->update([
            'is_active' => MetaFoxConstant::IS_INACTIVE,
        ]);
    }

    public function getDeviceTokensByUser(User $context, ?string $tokenId): array
    {
        $query = $this->getQueryDevicesByUser($context);

        if ($tokenId) {
            $query->whereNot('token_id', '=', $tokenId);
        }

        return $query->pluck('device_token')->toArray();
    }

    protected function getQueryDevicesByUser(User $context): Builder
    {
        return $this->getModel()->newModelQuery()
            ->where('user_id', '=', $context->entityId())
            ->where('is_active', '=', MetaFoxConstant::IS_ACTIVE);
    }
}
