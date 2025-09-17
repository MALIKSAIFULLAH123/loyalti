<?php

namespace MetaFox\FloodControl\Facades;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;

class FloodControl
{
    /**
     * @param User   $user
     * @param string $entityType
     * @param array  $extra
     */
    public function checkFloodControlWhenCreateItem(User $user, string $entityType, array $extra = []): void
    {
        $isEnabled = Settings::get('flood.enable', false);
        if (!$isEnabled) {
            return;
        }

        // time interval in minutes
        $intervalMinutes = (int) $user->getPermissionValue($entityType . '.flood_control');

        if ($intervalMinutes <= 0) {
            return;
        }

        $model = Relation::getMorphedModel($entityType);

        if (!$model) {
            return;
        }

        $data  = [
            'user_id' => $user->entityId(),
        ];
        $where = Arr::get($extra, 'where') ?? [];
        $query = $model::where(array_merge($where, $data));

        $lastItem = $query->orderByDesc('created_at')
            ->get(['created_at'])
            ->first();

        if (null === $lastItem) {
            return;
        }

        $waitEnd = $lastItem->created_at->addMinutes($intervalMinutes);

        if (Carbon::now()->greaterThan($waitEnd)) {
            return;
        }

        $waiting = (int) Carbon::now()->diffInSeconds($waitEnd);

        $error = json_encode([
            'title'   => __p('flood::phrase.limit_reached'),
            'message' => __p('flood::phrase.flood_control_invalid', [
                'waiting'  => $waiting,
                'interval' => $intervalMinutes,
            ]),
        ]);

        abort(403, $error);
    }
}
