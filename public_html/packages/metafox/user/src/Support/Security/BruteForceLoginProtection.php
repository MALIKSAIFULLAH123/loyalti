<?php

namespace MetaFox\User\Support\Security;

use Carbon\CarbonInterval;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Contracts\UserSecurityContract;
use MetaFox\User\Exceptions\ValidateUserException;

class BruteForceLoginProtection extends AbstractRateLimiter implements UserSecurityContract
{
    public const IP_FAILED_LOGINS_CACHE = 'ip_failed_logins_%s';

    protected string $timeCheck;

    /**
     * @return string
     */
    public function getTimeCheck(): string
    {
        return $this->timeCheck;
    }

    /**
     * @param string $timeCheck
     */
    public function setTimeCheck(string $timeCheck): void
    {
        $this->timeCheck = $timeCheck;
    }

    /**
     * @throws ValidateUserException
     */
    public function verify(?array $params = []): void
    {
        if (!$this->check($params)) {
            $seconds   = $this->getTimeCheck() - Carbon::now()->timestamp;
            $minutes   = $seconds / 60;
            $totalTime = CarbonInterval::make($minutes . 'm')->totalMinutes;
            $valueTime = CarbonInterval::minutes($totalTime);

            if ($minutes < 1) {
                $totalTime = CarbonInterval::make($seconds . 's')->totalSeconds;
                $valueTime = CarbonInterval::second($totalTime);
            }

            throw new ValidateUserException([
                'format'  => 'html',
                'title'   => __p('user::phrase.oops_login_failed'),
                'message' => __p('user::phrase.you_have_exceeded_the_allowed_number_of_login_attempts_please_try_again_after_value', [
                    'value_time' => $valueTime,
                ]),
            ]);
        }
    }

    public function check(?array $params = []): bool
    {
        $address         = Arr::get($params, 'address');
        $cacheName       = sprintf(self::IP_FAILED_LOGINS_CACHE, Str($address));
        $numberSetting   = Settings::get('user.brute_force_attempts_count', 0);
        $timeUnlocked    = Settings::get('user.brute_force_cool_down', 1);
        $numberTimeReset = Settings::get('user.brute_force_time_check', 0);
        $cacheValue      = $this->cache()->get($cacheName);

        $timeNow           = Carbon::now();
        $timestampNow      = $timeNow->timestamp;
        $timestampNowWhere = $timeNow->subMinutes($timeUnlocked)->timestamp;

        if (!$cacheValue) {
            return true;
        }

        $trialRecord = Arr::get($cacheValue, 'trail_record', []);

        if (empty($trialRecord)) {
            return true;
        }

        if (!Arr::has($cacheValue, 'time_check')) {
            return true;
        }

        $this->setTimeCheck($cacheValue['time_check']);

        $trialRecordCollection = collect($trialRecord);
        $totalTrial            = $trialRecordCollection->where('time', '>', $timestampNowWhere);

        if ($numberTimeReset > 0 && $trialRecordCollection->count() < $numberSetting && $cacheValue['time_reset'] <= $timestampNow) {
            $this->clearCache($address);

            return true;
        }

        if ($cacheValue['time_check'] >= $timestampNow) {
            return $totalTrial->count() < $numberSetting;
        }

        if ($trialRecordCollection->count() >= $numberSetting) {
            $this->clearCache($address);
        }

        return true;
    }

    public function process(?array $params = []): void
    {
        $numberTimeCheck    = Settings::get('user.brute_force_cool_down', 1);
        $numberLimitSetting = Settings::get('user.brute_force_attempts_count', 0);
        $numberTimeReset    = Settings::get('user.brute_force_time_check', 0);

        $address   = Arr::get($params, 'address');
        $cacheName = sprintf(self::IP_FAILED_LOGINS_CACHE, $address);

        $timestampReset = $numberTimeReset == 0
            ? Carbon::now()->timestamp
            : Carbon::now()->addMinutes($numberTimeReset)->timestamp;

        $attemptsCount = $this->cache()->get($cacheName, []);

        if ($numberTimeCheck == 0) {
            return;
        }

        $trialRecord = Arr::get($attemptsCount, 'trail_record', []);
        $timeCheck   = Arr::get($attemptsCount, 'time_check', Carbon::now()->addMinutes($numberTimeCheck)->timestamp);
        $timeReset   = Arr::get($attemptsCount, 'time_reset', $timestampReset);

        if (count($trialRecord) < $numberLimitSetting) {
            $attemptsCount['trail_record'][] = [
                'ip_address' => $address,
                'time'       => Carbon::now()->timestamp,
            ];

            $attemptsCount['time_check'] = $timeCheck;
            $attemptsCount['time_reset'] = $timeReset;

            $this->cache()->put($cacheName, $attemptsCount);
        }
    }

    /**
     * @inheritDoc
     */
    public function clearCache(string $address): void
    {
        $this->cache()->forget(sprintf(self::IP_FAILED_LOGINS_CACHE, $address));
    }
}
