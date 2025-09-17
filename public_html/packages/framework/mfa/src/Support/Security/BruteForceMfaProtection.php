<?php

namespace MetaFox\Mfa\Support\Security;

use Carbon\CarbonInterval;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use MetaFox\Mfa\Contracts\BruteForceMfaProtectionContract;
use MetaFox\Mfa\Notifications\BruteForceMfaNotification;
use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Exceptions\ValidateUserException;
use MetaFox\User\Models\User;
use MetaFox\User\Support\Security\AbstractRateLimiter;

class BruteForceMfaProtection extends AbstractRateLimiter implements BruteForceMfaProtectionContract
{
    public const USER_FAILED_MFA_CACHE = 'user_failed_mfa_%s';

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
        if ($this->check($params)) {
            return;
        }

        $this->sendNotification($params);
        $this->throwError();
    }

    public function check(?array $params = []): bool
    {
        $userId        = Arr::get($params, 'user_id');
        $numberSetting = Settings::get('mfa.brute_force_attempts_count', 5);
        $cacheName     = sprintf(self::USER_FAILED_MFA_CACHE, $userId);
        $timeSetting   = Settings::get('mfa.brute_force_cool_down', 0);

        $cacheValue = $this->cache()->get($cacheName);
        if (!$cacheValue) {
            return true;
        }

        $totalTrialRecord = count(Arr::get($cacheValue, 'trial_record', []));
        $timeCheck        = Arr::get($cacheValue, 'time_check');

        if ($totalTrialRecord == 0 || $timeCheck == null) {
            return true;
        }

        $this->setTimeCheck($timeCheck);

        if ($totalTrialRecord < $numberSetting) {
            return true;
        }

        if ($timeSetting == 0) {
            $this->sendNotification($params);

            return true;
        }

        if (Carbon::now()->lt(Carbon::parse($timeCheck))) {
            return false;
        }

        $this->clearCache($userId);

        return true;
    }

    public function process(?array $params = []): void
    {
        $numberSetting = Settings::get('mfa.brute_force_attempts_count', 5);
        $timeSetting   = Settings::get('mfa.brute_force_cool_down', 0);
        $userId        = Arr::get($params, 'user_id');
        $cacheName     = sprintf(self::USER_FAILED_MFA_CACHE, $userId);

        $attemptsCount = $this->cache()->get($cacheName, []);
        $trialRecord   = Arr::get($attemptsCount, 'trial_record', []);

        if (count($trialRecord) < $numberSetting) {
            $attemptsCount['trial_record'][] = ['user_id' => $userId];
            $attemptsCount['time_check']     = Carbon::now()->addHours($timeSetting)->timestamp;

            $this->cache()->put($cacheName, $attemptsCount);
        }
    }

    public function clearCache(int $userId): void
    {
        $this->cache()->forget(sprintf(self::USER_FAILED_MFA_CACHE, $userId));
    }

    protected function throwError(): void
    {
        $remainingTime = $this->getRemainingTime();
        if (!$remainingTime) {
            return;
        }

        throw new ValidateUserException([
            'format'  => 'html',
            'title'   => __p('user::phrase.oops_login_failed'),
            'message' => __p('user::phrase.you_have_exceeded_the_allowed_number_of_login_attempts_please_try_again_after_value', [
                'value_time' => $remainingTime,
            ]),
        ]);
    }

    protected function getRemainingTime(): ?CarbonInterval
    {
        $timeUserCanLogin = Carbon::createFromTimestamp($this->getTimeCheck());
        $now              = Carbon::now();

        return CarbonInterval::minutes($timeUserCanLogin->diffInMinutes($now));
    }

    protected function sendNotification(?array $params = []): void
    {
        $userId = Arr::get($params, 'user_id');
        $user   = User::find($userId);

        $notification = new BruteForceMfaNotification($user);
        $params       = [$user, $notification];

        Notification::send(...$params);
    }
}
