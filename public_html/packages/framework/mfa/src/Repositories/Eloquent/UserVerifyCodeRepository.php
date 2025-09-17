<?php

namespace MetaFox\Mfa\Repositories\Eloquent;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use MetaFox\Mfa\Contracts\OTPServiceInterface;
use MetaFox\Mfa\Models\UserService;
use MetaFox\Mfa\Models\UserVerifyCode;
use MetaFox\Mfa\Repositories\UserVerifyCodeRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Mfa\Support\Facades\Mfa;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class UserVerifyCodeRepository.
 */
class UserVerifyCodeRepository extends AbstractRepository implements UserVerifyCodeRepositoryInterface
{
    public function model()
    {
        return UserVerifyCode::class;
    }

    public function createUserVerifyCode(User $user, string $service, string $action, string $code): UserVerifyCode
    {
        $attributes     = $this->getFillableAttributes($user, $service, $action, $code);
        $userVerifyCode = new UserVerifyCode($attributes);

        $userVerifyCode->save();

        return $userVerifyCode;
    }

    public function resendVerification(UserService $userService, string $action): bool
    {
        if (!$this->shouldSendVerification($userService, $action)) {
            return false;
        }

        return $this->sendVerification($userService->user, $userService->service, $action);
    }

    public function shouldSendVerification(UserService $userService, string $action): bool
    {
        $userVerifyCode = $this->getUserVerifyCodeByUser(
            $userService->user,
            $userService->service,
            $action
        );

        if (!$userVerifyCode instanceof UserVerifyCode) {
            return true;
        }

        if ($userVerifyCode->isAuthenticated()) {
            return true;
        }

        if ($this->getRemainingTime($userVerifyCode)) {
            return false;
        }

        return true;
    }

    public function verifyCode(UserService $userService, string $action, string $code): bool
    {
        if (empty($code)) {
            return false;
        }

        $userVerifyCode = $this->getUserVerifyCodeByUser(
            $userService->user,
            $userService->service,
            $action
        );

        if (!$userVerifyCode instanceof UserVerifyCode) {
            return false;
        }

        if (empty($userVerifyCode->code)) {
            return false;
        }

        if ($userVerifyCode->isExpired()) {
            return false;
        }

        if (!Hash::check($this->addSuffixCode($code, $userService->service), $userVerifyCode->code)) {
            return false;
        }

        $userVerifyCode->onAuthenticated();

        return true;
    }

    public function sendVerification(User $user, string $service, $action): bool
    {
        $handler = Mfa::service($service);
        if (!$handler instanceof OTPServiceInterface) {
            return false;
        }

        $code = $handler->getVerifyCode();
        $this->handleUserVerifyCode($user, $service, $action, $this->addSuffixCode($code, $service));
        $handler->send($user, $code);

        return true;
    }

    public function getUserVerifyCodeByUser(User $user, string $service, string $action): ?UserVerifyCode
    {
        return UserVerifyCode::query()->where([
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
            'service'   => $service,
            'action'    => $action,
            'is_active' => 1,
        ])->first();
    }

    public function getRemainingTime(UserVerifyCode $userVerifyCode): int
    {
        $lastSentAt = Carbon::parse($userVerifyCode->last_sent_at);
        $now        = Carbon::now();

        $diffInSeconds = $lastSentAt->diffInSeconds($now);
        $delayTime     = Settings::get('mfa.resend_verification_delay_time', 60);

        return max(0, $delayTime - $diffInSeconds);
    }

    protected function addSuffixCode(string $code, string $service): string
    {
        return "$code.$service";
    }

    protected function handleUserVerifyCode(User $user, string $service, string $action, string $code): void
    {
        $this->deactivateUserVerifyCode($user, $service, $action);

        $userVerifyCode = $this->createUserVerifyCode($user, $service, $action, $code);

        $userVerifyCode->save();
    }

    protected function deactivateUserVerifyCode(User $user, string $service, string $action): void
    {
        UserVerifyCode::query()->where([
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
            'service'   => $service,
            'action'    => $action,
            'is_active' => 1,
        ])->update(['is_active' => 0]);
    }

    private function getFillableAttributes(User $user, string $service, string $action, string $code): array
    {
        $timeout = Settings::get('mfa.verify_code_timeout', 60);

        return [
            'user_id'          => $user->entityId(),
            'user_type'        => $user->entityType(),
            'service'          => $service,
            'action'           => $action,
            'code'             => Hash::make($code),
            'expired_at'       => Carbon::now()->addSeconds($timeout),
            'is_active'        => 1,
            'authenticated_at' => null,
            'last_sent_at'     => Carbon::now(),
        ];
    }
}
