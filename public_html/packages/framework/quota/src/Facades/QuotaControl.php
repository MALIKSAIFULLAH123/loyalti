<?php

namespace MetaFox\QuotaControl\Facades;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;

class QuotaControl
{
    /**
     * @param User   $user         Context User
     * @param string $entityType   Content Type / Resource Type
     * @param int    $quantityItem Quantity created at a time. Default: 1
     * @param array  $extra        Extra configurations
     *                             [
     *                             'message' => (string) Error message when failed check
     *                             'where' => (array) Supported where param
     *                             ]
     */
    public function checkQuotaControlWhenCreateItem(
        User   $user,
        string $entityType,
        int    $quantityItem = 1,
        array  $extra = [],
    ): void
    {
        $messageFormat = Arr::get($extra, 'messageFormat', 'json');
        $message       = Arr::get($extra, 'message') ?? __p('quota::phrase.quota_control_invalid', ['entity_type' => __p_type_key($entityType)]);

        foreach ($this->getTimeframes($entityType) as $arr) {
            $timeframe = Arr::get($arr, 'period');

            if (!$this->checkQuotaControlWhenCreateItemByTimeframe($user, $entityType, $timeframe, $quantityItem, $extra)) {
                $this->error($messageFormat, Arr::get($arr, 'message', $message));
            }
        }
    }

    protected function getTimeframes($entityType): array
    {
        return [
            [
                'period'  => MetaFoxConstant::TIMEFRAME_FOREVER,
                'message' => __p('quota::phrase.quota_control_invalid', ['entity_type' => __p_type_key($entityType)]),
            ],
            [
                'period'  => MetaFoxConstant::TIMEFRAME_MONTHLY,
                'message' => __p('quota::phrase.quota_control_invalid_monthly', ['entity_type' => __p_type_key($entityType)]),
            ],
            [
                'period'  => MetaFoxConstant::TIMEFRAME_DAILY,
                'message' => __p('quota::phrase.quota_control_invalid_daily', ['entity_type' => __p_type_key($entityType)]),
            ],
        ];

    }

    protected function checkQuotaControlWhenCreateItemByTimeframe(
        User    $user,
        string  $entityType,
        ?string $timeframe,
        int     $quantityItem = 1,
        array   $extra = [],
    ): bool
    {
        if (!Settings::get('quota.enable', false)) {
            return true;
        }

        if ($quantityItem < 1) {
            return true;
        }

        $permissionName = $timeframe != MetaFoxConstant::TIMEFRAME_FOREVER
            ? $entityType . '.quota_control_' . $timeframe
            : $entityType . '.quota_control';

        try {
            $itemQuota = (int) $user->getPermissionValue($permissionName);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return true;
        }

        if ($itemQuota <= 0) {
            return true;
        }

        $createdAt = Arr::get($extra, 'created_at');
        $totalItem = $this->handleTotalItem($user, $entityType, $timeframe, $createdAt, $extra);

        if (Arr::has($extra, 'second_extra')) {
            $secondExtra = Arr::get($extra, 'second_extra');
            $totalItem   += $this->handleTotalItem($user, Arr::get($secondExtra, 'entity_type'), $timeframe, $createdAt, $secondExtra);
        }

        if ($totalItem + $quantityItem <= $itemQuota) {
            return true;
        }

        return false;
    }

    protected function handleTotalItem(
        User    $user,
        string  $entityType,
        ?string $timeframe,
        ?string $createdAt,
        array   $extra = [],
    ): int
    {
        $model = Relation::getMorphedModel($entityType);
        if (!$model) {
            return true;
        }

        $data = [
            'user_id' => $user->entityId(),
        ];

        $where  = Arr::get($extra, 'where') ?? [];
        $column = Arr::get($extra, 'column', 'created_at');
        $now    = Carbon::now();

        if ($createdAt) {
            $now = Carbon::make($createdAt);
        }

        $query = $model::where(array_merge($where, $data));

        match ($timeframe) {
            MetaFoxConstant::TIMEFRAME_DAILY   => $query->whereDay($column, $now->day)
                ->whereMonth($column, $now->month)
                ->whereYear($column, $now->year),
            MetaFoxConstant::TIMEFRAME_MONTHLY => $query->whereMonth($column, $now->month)
                ->whereYear($column, $now->year),
            default                            => $query
        };

        return $query->count();
    }

    private function error(string $messageFormat, string $message): void
    {
        $errorCode = 403;
        $error     = json_encode([
            'title'   => __p('quota::phrase.limit_reached'),
            'message' => $message,
        ]);

        if ($messageFormat == 'text') {
            $error     = $message;
            $errorCode = 422;
        }

        abort($errorCode, $error);
    }
}
