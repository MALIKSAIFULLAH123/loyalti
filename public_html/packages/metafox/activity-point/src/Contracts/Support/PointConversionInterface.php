<?php
namespace MetaFox\ActivityPoint\Contracts\Support;

use Illuminate\Support\Carbon;
use MetaFox\Platform\Contracts\User;

interface PointConversionInterface
{
    /**
     * @return array
     */
    public function getConversionCurrencies(): array;

    /**
     * @param User $user
     * @return int
     */
    public function getTotalPointsPerDay(User $user): int;

    /**
     * @param User $user
     * @return int
     */
    public function getTotalPointsPerMonth(User $user): int;

    /**
     * @param User $user
     * @return int
     */
    public function getExchangedPointsInYear(User $user): int;

    /**
     * @param User $user
     * @return int
     */
    public function getExchangedPointsInMonth(User $user): int;

    /**
     * @param User $user
     * @return int
     */
    public function getPendingConversionPoints(User $user): int;

    /**
     * @param User $user
     * @return int
     */
    public function getAvailableUserPoints(User $user): int;

    /**
     * @param int    $points
     * @param string $currency
     * @return float
     */
    public function getConversionAmount(int $points, string $currency): float;

    /**
     * @param float $total
     * @return float
     */
    public function getCommissionFee(float $total): float;

    /**
     * @param string $currency
     * @return string|null
     */
    public function getExchangeRateFormat(string $currency): ?string;

    /**
     * @return array
     */
    public function getConversionRequestStatusOptions(): array;

    /**
     * @param Carbon $start
     * @param Carbon $end
     * @return int
     */
    public function aggregateConversionRequest(Carbon $start, Carbon $end): int;

    /**
     * @param User $user
     * @return int
     */
    public function getMaxPointsCanCreate(User $user): int;

    /**
     * @param User $user
     * @return int
     */
    public function getMinPointsCanCreate(User $user): int;

    /**
     * @param User $user
     * @return int|null
     */
    public function getRestPointsPerDay(User $user): ?int;

    /**
     * @param User $user
     * @return int|null
     */
    public function getRestPointsPerMonth(User $user): ?int;
}
