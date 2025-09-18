<?php

namespace MetaFox\Invite\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Invite\Models\Invite;
use MetaFox\Invite\Models\InviteCode;
use MetaFox\Invite\Policies\InvitePolicy;
use MetaFox\Invite\Repositories\InviteCodeRepositoryInterface;
use MetaFox\Invite\Repositories\InviteRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;


class InviteOnlyRule implements Rule, DataAwareRule
{
    /**
     * AllowInRule constructor.
     */
    public function __construct() {}

    /**
     * @var array
     */
    protected array $data = [];

    public function setData($data): void
    {
        $this->data = $data;
    }


    protected string $message;

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        if (null === $value) {
            $this->setMessage(__p('invite::validation.the_invite_code_is_required'));

            return !Settings::get('invite.invite_only', false);
        }

        $code = Arr::get($this->data, 'code');

        if ($code) {
            return $this->handleByCode($value);
        }

        return $this->validateByInviteCode($value);
    }

    /**
     * @param string $code
     * @return bool
     */
    private function validateByInviteCode(string $code): bool
    {
        $inviteCode = $this->codeRepository()->getCodeByValue($code);

        if (!$inviteCode instanceof InviteCode) {
            $this->setMessage(__p('invite::validation.the_invite_code_is_incorrect'));
            return false;
        }

        if (null !== $inviteCode->expired_at && Carbon::parse($inviteCode->expired_at)->lessThanOrEqualTo(Carbon::now())) {
            $this->setMessage(__p('invite::validation.the_invite_code_has_expired'));
            return false;
        }

        $inviter = $inviteCode->user;

        if (!$inviter instanceof User || !policy_check(InvitePolicy::class, 'create', $inviter)) {
            $this->setMessage(__p('invite::validation.the_invite_code_is_incorrect'));
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    protected function inviteRepository(): InviteRepositoryInterface
    {
        return resolve(InviteRepositoryInterface::class);
    }

    protected function codeRepository(): InviteCodeRepositoryInterface
    {
        return resolve(InviteCodeRepositoryInterface::class);
    }

    protected function handleByCode(string $inviteCode): bool
    {
        $code = Arr::get($this->data, 'code');

        $invite = $this->inviteRepository()->getBuilderByCode($code)
            ->where('status_id', Invite::INVITE_PENDING)
            ->first();

        if (!$invite instanceof Invite || $invite->invite_code != $inviteCode) {
            return $this->validateByInviteCode($inviteCode);
        }

        return $this->validateByInvite($invite);
    }

    protected function validateByInvite(Invite $invite): bool
    {
        $nowTimestamp = Carbon::now()->timestamp;
        $inviter      = $invite->user;

        if (!$inviter instanceof User || !policy_check(InvitePolicy::class, 'create', $inviter)) {
            $this->setMessage(__p('invite::validation.the_invite_code_is_incorrect'));
            return false;
        }

        if (isset($invite->expired_at) && strtotime($invite->expired_at) < $nowTimestamp) {
            $this->setMessage(__p('invite::validation.the_invitation_does_not_exist_or_has_expired'));
            return false;
        }

        return true;
    }
}
