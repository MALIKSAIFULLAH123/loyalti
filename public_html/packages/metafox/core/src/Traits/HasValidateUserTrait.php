<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use MetaFox\Ban\Facades\Ban;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\UserRole;
use MetaFox\User\Exceptions\ValidateUserException;
use MetaFox\User\Models\User;
use MetaFox\User\Models\UserBan;
use MetaFox\User\Models\UserVerify;

/**
 * Trait HasValidateUserTrait.
 *
 * @mixin AbstractRepository
 */
trait HasValidateUserTrait
{
    public function getVerifyBy(User $user, string $value): ?string
    {
        $isVerifyEmail = $user->phone_number != $value;

        if ($isVerifyEmail && $user->shouldVerifyEmailAddress()) {
            return 'email';
        }

        if ($user->shouldVerifyPhoneNumber()) {
            return 'phone_number';
        }

        return null;
    }

    public function validateVerifiedBy(User $user, string $verifyBy): void
    {
        match ($verifyBy) {
            'email'        => $this->validateEmailAddress($user),
            'phone_number' => $this->validatePhoneNumber($user),
        };
    }

    public function validateStatuses(User $user): void
    {
        app('events')->dispatch('user.validate_status', [$user]);

        $this->validateBanRules($user);
        $this->validateBanned($user);
        $this->validateApproved($user);
        $this->validateVerified($user);
    }

    protected function validateBanRules(User $user): void
    {
        Ban::validateMultipleType($user);
    }

    private function validateBanned(User $user): void
    {
        $userBanned = $this->getBan($user->entityId());

        if ($userBanned instanceof UserBan) {
            throw new ValidateUserException($this->getBannedParams($userBanned));
        }

        if ($user->hasRole(UserRole::BANNED_USER)) {
            throw new ValidateUserException($this->getBannedParams());
        }
    }

    private function getBannedParams(?UserBan $userBanned): array
    {
        $reason = $userBanned?->reason;
        $time   = $userBanned?->end_time_stamp ?? 0;
        $isDate = $time != 0;

        $messageText = $this->isOldMobileVersion()
            ? $this->getMessageTextForOldMobileVersion($reason, $time) /*** @deprecated Need remove for some next version */
            : 'banned_user_desc_text';

        if ($isDate) {
            $time = Carbon::parse($time)->format('c');
        }

        return [
            'title'        => __p('user::phrase.banned_user'),
            'message'      => 'banned_user_desc',
            'message_text' => $messageText,
            'arguments'    => [
                'message' => [
                    [
                        'key'   => 'reason',
                        'value' => $this->transformBannedReason($reason),
                    ],
                    [
                        'key'   => 'hasReason',
                        'value' => (int) !empty($reason),
                    ],
                    [
                        'key'     => 'time',
                        'value'   => $time,
                        'is_date' => $isDate,
                    ],
                ],
            ],
        ];
    }

    private function transformBannedReason(?string $reason): ?string
    {
        if (!$reason || MetaFox::isMobile()) {
            return $reason;
        }

        return sprintf('<div class=\'mh-scroll-200\'>%s</div>', $reason);
    }

    /**
     * @deprecated Need remove for some next version
     */
    protected function getMessageTextForOldMobileVersion(?string $reason, int $time): string
    {
        if ($time) {
            $locale = App::getLocale();
            $time   = Carbon::parse($time)->locale($locale)->format('Y-M-D H:i');
        }

        $phraseParams = [
            'reason'    => $reason,
            'hasReason' => (int) !empty($reason),
            'time'      => $time,
        ];

        return __p('user::web.banned_user_desc_text', $phraseParams);
    }

    /**
     * @deprecated Need remove for some next version
     */
    protected function isOldMobileVersion(): bool
    {
        return MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.6', '<');
    }

    private function validateApproved(User $user): void
    {
        if ($user->isApproved()) {
            return;
        }

        throw new ValidateUserException([
            'title'   => __p('user::phrase.pending_accounts'),
            'message' => __p('user::phrase.your_account_is_now_waiting_for_approval'),
        ]);
    }

    private function validateEmailAddress(User $user)
    {
        if (!$user->mustVerifyEmailAddress()) {
            return;
        }

        throw new ValidateUserException([
            'title'   => __p('user::phrase.pending_email_verification_title'),
            'message' => __p('user::phrase.pending_email_verification'),
            'action'  => 'user/verify',
            'payload' => [
                'action'  => UserVerify::ACTION_EMAIL,
                'user_id' => $user->id,
                'email'   => $user->email,
            ],
        ]);
    }

    private function validatePhoneNumber(User $user): void
    {
        if (!$user->mustVerifyPhoneNumber()) {
            return;
        }

        throw new ValidateUserException([
            'title'   => __p('user::phrase.pending_phone_number_verification_title'),
            'message' => __p('user::phrase.pending_phone_number_verification'),
            'action'  => 'user/verify',
            'payload' => [
                'action'       => UserVerify::ACTION_PHONE_NUMBER,
                'user_id'      => $user->id,
                'phone_number' => $user->phone_number,
            ],
        ]);
    }

    private function validateVerified(User $user)
    {
        if ($user->hasVerified()) {
            return;
        }

        throw new ValidateUserException([
            'title'   => __p('user::phrase.pending_account_verification_title'),
            'message' => __p('user::phrase.pending_account_verification'),
        ]);
    }
}
