<?php
namespace MetaFox\ActivityPoint\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\ActivityPoint\Contracts\Support\PointConversionInterface;
use MetaFox\ActivityPoint\Models\PointStatistic;
use MetaFox\ActivityPoint\Repositories\PointStatisticRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Support\Facades\UserValue;
use MetaFox\ActivityPoint\Models\ConversionAggregate;
use MetaFox\ActivityPoint\Models\ConversionRequest;
use MetaFox\ActivityPoint\Repositories\ConversionStatisticRepositoryInterface;
use MetaFox\ActivityPoint\Support\PointConversion as Support;

class PointConversion implements PointConversionInterface
{
    public const TRANSACTION_STATUS_PENDING = 'pending';
    public const TRANSACTION_STATUS_APPROVED = 'approved';
    public const TRANSACTION_STATUS_DENIED = 'denied';
    public const TRANSACTION_STATUS_CANCELLED = 'cancelled';

    public const DEFAULT_CONVERSION_RATE_CURRENCY_TO_MONEY = 'USD';

    private array $grantedCurrencies = ['USD'];

    public function getConversionCurrencies(): array
    {
        $currencies = app('currency')->getCurrencies();

        if (!count($currencies)) {
            return [];
        }

        $currencies = array_filter($currencies, function ($currency) {
            return in_array(Arr::get($currency, 'code'), $this->grantedCurrencies);
        });

        return array_map(function ($currency) {
            return [
                'label' => Arr::get($currency, 'name'),
                'code'  => Arr::get($currency, 'code'),
                'value' => Arr::get($currency, 'code'),
            ];
        }, $currencies);
    }

    public function getTotalPointsPerDay(User $user): int
    {
        return (int)ConversionRequest::query()
            ->where('user_id', '=', $user->entityId())
            ->whereIn('status', [Support::TRANSACTION_STATUS_PENDING, Support::TRANSACTION_STATUS_APPROVED])
            ->where('created_at', '<=', Carbon::now()->endOfDay())
            ->where('created_at', '>=', Carbon::now()->startOfDay())
            ->sum('points');
    }

    public function getTotalPointsPerMonth(User $user): int
    {
        return (int) ConversionRequest::query()
            ->where('user_id', $user->entityId())
            ->whereIn('status', [Support::TRANSACTION_STATUS_PENDING, Support::TRANSACTION_STATUS_APPROVED])
            ->where('created_at', '<=', Carbon::now()->endOfMonth())
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->sum('points');
    }

    public function getExchangedPointsInYear(User $user): int
    {
        $aggregate = ConversionAggregate::query()
            ->where([
                'user_id'   => $user->entityId(),
                'user_type' => $user->entityType(),
            ])
            ->where('date', '=', Carbon::now()->startOfYear())
            ->first();

        if (null === $aggregate) {
            return 0;
        }

        return $aggregate->total;
    }

    public function getExchangedPointsInMonth(User $user): int
    {
        $aggregate = ConversionAggregate::query()
            ->where([
                'user_id'   => $user->entityId(),
                'user_type' => $user->entityType(),
            ])
            ->where('date', '=', Carbon::now()->startOfMonth())
            ->first();

        if (null === $aggregate) {
            return 0;
        }

        return $aggregate->total;
    }

    public function getPendingConversionPoints(User $user): int
    {
        $statistic = resolve(ConversionStatisticRepositoryInterface::class)->getStatistic($user);

        if (null === $statistic) {
            return 0;
        }

        return $statistic->total_pending;
    }

    public function getAvailableUserPoints(User $user): int
    {
        /**
         * @var PointStatistic $statistic
         */
        $statistic = resolve(PointStatisticRepositoryInterface::class)->viewStatistic($user, $user->entityId());

        return $statistic->available_points;
    }

    public function getConversionAmount(int $points, string $currency): float
    {
        $rate = (float) Settings::get(sprintf('activitypoint.conversion_rate.%s', $currency), 0);

        if ($rate <= 0) {
            return 0;
        }

        return round($points * $rate, 2);
    }

    public function getCommissionPercentage(): float
    {
        return (float) Settings::get('activitypoint.conversion_request_fee', 0);
    }

    public function getCommissionFee(float $total): float
    {
        $percentage = $this->getCommissionPercentage();

        if ($percentage == 0) {
            return 0;
        }

        return round(($total * $percentage) / 100, 2);
    }

    public function getExchangeRateFormat(string $currency = self::DEFAULT_CONVERSION_RATE_CURRENCY_TO_MONEY): ?string
    {
        $rate = (float)Settings::get(sprintf('activitypoint.conversion_rate.%s', $currency), 0);

        if ($rate <= 0) {
            return null;
        }

        return app('currency')->getPriceFormatByCurrencyId($currency, $rate);
    }

    public function getConversionRequestStatusOptions(): array
    {
        return [
            [
                'label' => __p('core::phrase.pending'),
                'value' => self::TRANSACTION_STATUS_PENDING,
            ],
            [
                'label' => __p('activitypoint::phrase.approved'),
                'value' => self::TRANSACTION_STATUS_APPROVED,
            ],
            [
                'label' => __p('activitypoint::phrase.cancelled'),
                'value' => self::TRANSACTION_STATUS_CANCELLED,
            ],
            [
                'label' => __p('activitypoint::phrase.denied'),
                'value' => self::TRANSACTION_STATUS_DENIED,
            ],
        ];
    }

    public function aggregateConversionRequest(Carbon $start, Carbon $end): int
    {
        return ConversionRequest::query()
            ->where([
                'status' => self::TRANSACTION_STATUS_APPROVED,
                ['created_at', '<=', $end],
                ['created_at', '>=', $start],
            ])
            ->sum('points');
    }

    public function getMaxPointsCanCreate(User $user): int
    {
        $currentPoints = $this->getAvailableUserPoints($user);

        if ($currentPoints <= 0) {
            return 0;
        }

        $max = (int)$user->getPermissionValue(sprintf('%s.max_points_for_conversion', ConversionRequest::ENTITY_TYPE));
        $totalPerDay = PointConversion::getTotalPointsPerDay($user);
        $totalPerMonth = PointConversion::getTotalPointsPerMonth($user);
        $maxPerDay = (int) $user->getPermissionValue('activitypoint_conversion_request.max_points_per_day');
        $maxPerMonth = (int) $user->getPermissionValue('activitypoint_conversion_request.max_points_per_month');

        if (0 == $max || $max > $currentPoints) {
            $max = $currentPoints;
        }

        if (0 == $maxPerDay && 0 == $maxPerMonth) {
            return $max;
        }

        if ($maxPerMonth) {
            $rest = $maxPerMonth > $totalPerMonth ? $maxPerMonth - $totalPerMonth : 0;

            if ($max > $rest) {
                $max = $rest;
            }
        }

        if ($maxPerDay) {
            $rest = $maxPerDay > $totalPerDay ? $maxPerDay - $totalPerDay : 0;

            if ($max > $rest) {
                $max = $rest;
            }
        }

        return $max;
    }

    public function getMinPointsCanCreate(User $user): int
    {
        $min = 1;

        $minPointsForCreate = (int) $user->getPermissionValue(sprintf('%s.min_points_for_conversion', ConversionRequest::ENTITY_TYPE));

        if ($minPointsForCreate) {
            $min = $minPointsForCreate;
        }

        return $min;
    }

    public function getRestPointsPerDay(User $user): ?int
    {
        $maxPerDay = (int) $user->getPermissionValue('activitypoint_conversion_request.max_points_per_day');

        if ($maxPerDay < 0) {
            return 0;
        }

        if (0 === $maxPerDay) {
            return null;
        }

        $totalPerDay = PointConversion::getTotalPointsPerDay($user);

        if ($maxPerDay > $totalPerDay) {
            return $maxPerDay - $totalPerDay;
        }

        return 0;
    }

    public function getRestPointsPerMonth(User $user): ?int
    {
        $maxPerMonth = (int) $user->getPermissionValue('activitypoint_conversion_request.max_points_per_month');

        if ($maxPerMonth < 0) {
            return 0;
        }

        if (0 === $maxPerMonth) {
            return null;
        }

        $totalPerMonth = PointConversion::getTotalPointsPerMonth($user);

        if ($maxPerMonth > $totalPerMonth) {
            return $maxPerMonth - $totalPerMonth;
        }

        return 0;
    }
}
