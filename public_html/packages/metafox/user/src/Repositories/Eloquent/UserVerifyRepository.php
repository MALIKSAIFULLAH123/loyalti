<?php

namespace MetaFox\User\Repositories\Eloquent;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Exceptions\VerifyCodeException;
use MetaFox\User\Jobs\VerifyEmailJob;
use MetaFox\User\Jobs\VerifyPhoneNumberJob;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\User as UserModel;
use MetaFox\User\Models\UserVerify;
use MetaFox\User\Repositories\UserVerifyRepositoryInterface;
use MetaFox\User\Support\Facades\UserVerify as UserVerifyFacade;

class UserVerifyRepository extends AbstractRepository implements UserVerifyRepositoryInterface
{
    public function model()
    {
        return UserVerify::class;
    }

    public function generate(User $user, string $action, string $verifiable, string $code): ?UserVerify
    {
        $hash    = sha1($this->addSuffixCode($code, $action));
        $timeout = Settings::get('user.verification_timeout', 60);

        if (!$timeout) {
            $timeout = 60;
        }

        $verification = $this->create([
            'user_id'    => $user->entityId(),
            'user_type'  => $user->entityType(),
            'action'     => $action,
            'verifiable' => $verifiable,
            'hash_code'  => $hash,
            'expires_at' => Carbon::now()->addMinutes($timeout),
        ]);

        $verification->save();

        return $verification;
    }

    public function getVerifyCode(string $action): string
    {
        $codeLength = 6;
        $code       = str_pad(random_int(0, str_repeat(9, $codeLength)), $codeLength, '0', STR_PAD_LEFT);
        $hash       = sha1($this->addSuffixCode($code, $action));

        $isExistCode = $this->getModel()->newQuery()->where('hash_code', $hash)->exists();
        if ($isExistCode) {
            return $this->getVerifyCode($action);
        }

        return $code;
    }

    public function resend(array $params): void
    {
        $userId = Arr::get($params, 'user_id');

        $user = UserModel::find($userId);
        if (!$user instanceof UserModel) {
            throw new VerifyCodeException(['title' => __p('user::phrase.user_does_not_exist')]);
        }

        $action          = Arr::get($params, 'action');
        $verifiableField = UserVerifyFacade::getVerifiableField($action);
        $verifiableValue = Arr::get($params, $verifiableField);
        $verifyAtField   = UserVerifyFacade::getVerifyAtField($action);

        if (!$this->shouldResend($userId, $verifiableField, $verifiableValue, $verifyAtField)) {
            throw new VerifyCodeException(['title' => __p('user::phrase.account_does_not_exist_or_has_been_verified')]);
        }

        UserVerifyFacade::web($action)->resend($user, $verifiableValue);
    }

    /**
     * @deprecated Need remove for some next version
     * */
    public function resendLink(User $user, string $action, string $verifiableValue): void
    {
        if ($action == UserVerify::ACTION_EMAIL && $user->hasVerifiedEmail()) {
            abort(400, __p('user::validation.account_verified'));
        }

        if ($action == UserVerify::ACTION_PHONE_NUMBER && $user->hasVerifiedPhoneNumber()) {
            abort(400, __p('user::validation.account_verified'));
        }

        UserVerifyFacade::web($action)->resend($user, $verifiableValue);
    }

    protected function shouldResend(int $userId, string $verifiableField, string $verifiableValue, string $verifyAtField): bool
    {
        $user       = $this->getUserByVerifiableField($verifiableField, $verifiableValue);
        $userVerify = $this->getLatestUserVerifyByUserId($userId, $verifiableValue);

        $userVerifyAtValue = $user?->{$verifyAtField} ?? null;

        if (!$userVerify) {
            return $user && !$userVerifyAtValue;
        }

        if (!$userVerify->user) {
            return false;
        }

        if ($user && $userVerifyAtValue) {
            return false;
        }

        return true;
    }

    protected function getLatestUserVerifyByUserId(int $userId, string $verifiableValue): ?UserVerify
    {
        return $this->getModel()->newQuery()
            ->where('user_id', $userId)
            ->where('verifiable', $verifiableValue)
            ->where('is_verified', 0)
            ->latest()
            ->first();
    }

    protected function getUserByVerifiableField(string $verifiableField, string $verifiableValue): ?User
    {
        return UserModel::query()
            ->where($verifiableField, $verifiableValue)
            ->first();
    }

    public function commonVerify(?UserVerify $verify): void
    {
        if (!$verify instanceof UserVerify) {
            $message = json_encode([
                'title'   => __p('user::phrase.verification_code_not_found'),
                'message' => __p('user::phrase.verification_code_not_found_desc'),
            ]);

            abort(400, $message);
        }

        $user = $verify->user;
        if (!$user instanceof User || $verify->is_verified) {
            throw new VerifyCodeException([
                'title'   => __p('core::phrase.content_is_not_available'),
                'message' => __p('core::phrase.content_is_not_available'),
            ]);
        }

        if (!Auth::guest()) {
            $context = user();
            if ($context->entityId() > 0 && !$verify->isUser($context)) {
                throw new VerifyCodeException([
                    'title'   => __p('core::phrase.content_is_not_available'),
                    'message' => __p('core::phrase.content_is_not_available'),
                ]);
            }
        }

        if ($verify->expires_at < Carbon::now()) {
            $message = json_encode([
                'title'   => __p('user::phrase.verification_code_is_expired'),
                'message' => __p('user::phrase.verification_code_is_expired_desc'),
            ]);

            abort(400, $message);
        }

        $this->checkHasVerifyVerifiable($verify);
    }

    public function checkResend(User $user, string $action): void
    {
        $remainingTime = $this->getRemainingTime($user, $action);
        if ($remainingTime) {
            abort(400, __p('user::phrase.must_wait_to_resend_verification', [
                'duration' => $remainingTime,
            ]));
        }
    }

    public function sendVerificationEmail(User $user, string $verifiable): void
    {
        VerifyEmailJob::dispatch($user, $verifiable);
    }

    public function sendVerificationPhoneNumber(User $user, string $verifiable): void
    {
        VerifyPhoneNumberJob::dispatch($user, $verifiable);
    }

    public function invalidatePendingVerify(User $user, string $action)
    {
        return $this->getPendingQuery($user, $action)->update([
            'expires_at' => Carbon::now(),
        ]);
    }

    public function cleanupPending()
    {
        $maxPendingVerificationDuration = (int) Settings::get('user.days_for_delete_pending_user_verification', 0);

        if (!$maxPendingVerificationDuration) {
            return;
        }

        UserModel::query()
            ->whereNull('verified_at')
            ->where('created_at', '<=', Carbon::now()->subDays($maxPendingVerificationDuration))
            ->each(function ($user) {
                $user?->delete();
            });
    }

    public function addSuffixCode(string $code, string $action): string
    {
        return "$code.$action";
    }

    protected function checkHasVerifyVerifiable(UserVerify $verify): void
    {
        $verifiableField = UserVerifyFacade::getVerifiableField($verify->action);
        $user            = $this->getUserByVerifiableField($verifiableField, $verify->verifiable);

        if (!$user) {
            return;
        }

        $verifyMethodName = $verifiableField == 'email' ? 'hasVerifiedEmail' : 'hasVerifiedPhoneNumber';
        if (!method_exists($user, $verifyMethodName)) {
            return;
        }

        if (!$user->{$verifyMethodName}()) {
            return;
        }

        throw new VerifyCodeException([
            'title' => __p('user::phrase.your_email_phone_number_has_been_used'),
        ]);
    }

    private function getPendingQuery(User $user, string $action): Builder
    {
        return $this->query()
            ->where('expires_at', '>=', Carbon::now())
            ->where([
                'user_id'   => $user->entityId(),
                'user_type' => $user->entityType(),
                'action'    => $action,
            ]);
    }

    public function getRemainingTime(User $user, string $action): int
    {
        $latestRecord = $this->query()
            ->where([
                'user_id'   => $user->entityId(),
                'user_type' => $user->entityType(),
                'action'    => $action,
            ])
            ->latest('created_at')
            ->first();

        if (!$latestRecord) {
            return 0;
        }

        $delay     = Settings::get('user.resend_verification_delay_time', 15);
        $createdAt = Carbon::parse($latestRecord->created_at);
        $now       = Carbon::now();

        $diffInMinutes = $createdAt->diffInMinutes($now);

        return max(0, $delay - $diffInMinutes);
    }

    public function mustVerifyEmail(?string $email, array $attributes): bool
    {
        if (!Settings::get('user.verify_after_changing_email')) {
            return false;
        }

        if (!Arr::has($attributes, 'email')) {
            return false;
        }

        $newEmail = Arr::get($attributes, 'email');
        if (!$newEmail) {
            return false;
        }

        if ($email == $newEmail) {
            return false;
        }

        return true;
    }

    public function mustVerifyPhoneNumber(?string $phoneNumber, array $attributes): bool
    {
        if (!Settings::get('user.verify_after_changing_phone_number')) {
            return false;
        }

        if (!Arr::has($attributes, 'phone_number')) {
            return false;
        }

        $newPhoneNumber = Arr::get($attributes, 'phone_number');
        if (!$newPhoneNumber) {
            return false;
        }

        if ($phoneNumber == $newPhoneNumber) {
            return false;
        }

        return true;
    }
}
