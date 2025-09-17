<?php

namespace MetaFox\Firebase\Listeners;

use Illuminate\Http\Request;
use MetaFox\Authorization\Repositories\DeviceRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class UserLogoutListener
{
    public function handle(?User $user, Request $request): void
    {
        $deviceId = $request->get('device_uid');

        if (!$deviceId || !$user) {
            return;
        }

        $tokens = resolve(DeviceRepositoryInterface::class)->deleteDeviceById($user, $deviceId);

        if (is_array($tokens)) {
            app('firebase.fcm')->removeUserDeviceGroup($user->entityId(), $tokens);
        }
    }
}
