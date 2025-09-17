<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Invite\Listeners;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Invite\Models\Invite;
use MetaFox\Invite\Models\InviteCode;
use MetaFox\Invite\Models\InviteTransaction;
use MetaFox\Invite\Repositories\InviteCodeRepositoryInterface;
use MetaFox\Invite\Repositories\InviteRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Models\User as UserModel;

/**
 * Class UpdatedInviteListener.
 * @ignore
 * @codeCoverageIgnore
 */
class UpdatedInviteListener
{
    public function __construct(
        protected InviteRepositoryInterface     $repository,
        protected InviteCodeRepositoryInterface $codeRepository
    ) {}

    /**
     * @param UserModel   $user
     * @param string|null $code
     * @param array       $attributes
     * @return Invite|null
     */
    public function handle(UserModel &$user, ?string $code = null, array $attributes = []): ?Invite
    {
        $code       = Arr::get($attributes, 'code');
        $inviteCode = Arr::get($attributes, 'invite_code');

        $invite = match (null === $inviteCode) {
            true    => $this->handleByOwner($user, $attributes),
            default => $this->handleByCode($user, $attributes)
        };

        if ($code) {
            $this->handleAutoVerifyUser($user, $code);
        }

        return $invite;
    }

    protected function handleByCode(User $userInvited, array $attributes): ?Invite
    {
        $inviteCode = Arr::get($attributes, 'invite_code');
        $query      = $this->repository->getBuilderByInviteCode($inviteCode);
        $query      = $this->buildQueryInvite($userInvited, $query);
        $invite     = $query->first();

        if (!$invite instanceof Invite) {
            $invite = $this->handleCreateInvite($userInvited, $inviteCode);

            if (!$invite instanceof Invite) {
                return null;
            }
        }

        if (!$userInvited->isApproved()) {
            if (!Settings::get('invite.auto_approve_user_registered', false)) {
                $invite->update(['owner_id' => $userInvited->entityId(), 'owner_type' => $userInvited->entityType()]);

                return $invite;
            }

            $userInvited->update(['approve_status' => MetaFoxConstant::STATUS_APPROVED]);

            $userInvited->refresh();
        }

        $pending = app('events')->dispatch('subscription.invoice.has_pending', [$userInvited], true);

        if ($pending) {
            $invite->update(['owner_id' => $userInvited->entityId(), 'owner_type' => $userInvited->entityType()]);

            return $invite;
        }

        $invite->update([
            'status_id'  => Invite::INVITE_COMPLETED,
            'owner_id'   => $userInvited->entityId(),
            'owner_type' => $userInvited->entityType(),
        ]);

        $this->increaseUserActivityPoint($invite);

        $this->handleFriendWithTheirHost($userInvited, $invite->user);

        return $invite;
    }

    protected function handleByOwner(User $userInvited, array $attributes): ?Invite
    {
        $inviteCode = Arr::get($attributes, 'invite_code');

        $invite = $this->repository->getModel()->newQuery()
            ->where('owner_id', $userInvited->entityId())
            ->where('owner_type', $userInvited->entityType())
            ->where('status_id', Invite::INVITE_PENDING)
            ->first();

        if (!$userInvited->isApproved()) {
            return null;
        }

        if (!$invite instanceof Invite) {
            if ($inviteCode == null) {
                return null;
            }

            $invite = $this->handleCreateInvite($userInvited, $inviteCode);

            if (!$invite instanceof Invite) {
                return null;
            }
        }

        $invite->update([
            'status_id' => Invite::INVITE_COMPLETED,
        ]);

        $this->increaseUserActivityPoint($invite);

        $this->handleFriendWithTheirHost($userInvited, $invite->user);

        return $invite;
    }

    private function increaseUserActivityPoint(Invite $model): void
    {
        $user = $model->user;
        if (!$user instanceof User) {
            return;
        }

        $transactionModel = new InviteTransaction();
        $dataTransaction  = [
            'address' => $model->email ?? $model->phone_number,
            'action'  => Invite::ACTION_COMPLETED,
        ];

        if ($transactionModel->query()->where($dataTransaction)->exists()) {
            return;
        }

        $transactionModel->fill($dataTransaction)->save();

        app('events')->dispatch('activitypoint.increase_user_point', [$user, $model, Invite::ACTION_COMPLETED]);
    }

    private function handleFriendWithTheirHost(User $user, User $host): void
    {
        if (!Settings::get('invite.make_invited_users_friends_with_their_host')) {
            return;
        }

        if (!app('events')->dispatch('friend.can_add_friend', [$host, $user])) {
            return;
        }

        app('events')->dispatch('user.registration.make_invited_users_friends', [$user, $host]);
    }

    private function handleAutoVerifyUser(UserModel &$userInvited, string $code): void
    {
        $query  = $this->repository->getBuilderByCode($code);
        $query  = $this->buildQueryInvite($userInvited, $query);
        $invite = $query->first();

        if (!$invite instanceof Invite) {
            return;
        }

        if ($invite->expired_at) {
            $expiredTimestamp = Carbon::make($invite->expired_at)->timestamp;
            if ($expiredTimestamp < Carbon::now()->timestamp) {
                return;
            }
        }

        if ($userInvited->hasVerified()) {
            return;
        }

        if ($invite->email) {
            $userInvited->markEmailAsVerified();
        }

        if ($invite->phone_number) {
            $userInvited->markPhoneNumberAsVerified();
        }

        $userInvited->markAsVerified();
    }

    protected function handleCreateInvite(User $userInvited, string $code): ?Model
    {
        $inviteCode = $this->codeRepository->getCodeByValue($code);
        if (!$inviteCode instanceof InviteCode) {
            return null;
        }

        $keyGenerate = sprintf(
            Invite::INVITE_KEY_FORMAT,
            $inviteCode->user->getEmailForVerification(),
            $userInvited->getEmailForVerification() ?? $userInvited->phone_number,
            null
        );

        $dataInsert = [
            'invite_code'  => $inviteCode->code,
            'phone_number' => $userInvited->phone_number,
            'email'        => $userInvited->getEmailForVerification(),
            'user_id'      => $inviteCode->userId(),
            'user_type'    => $inviteCode->userType(),
            'code'         => md5($keyGenerate),
            'expired_at'   => null,
            'message'      => null,
        ];

        $invite = $this->repository->getModel()->fill($dataInsert);
        $invite->save();

        $this->handleCancelInvite($userInvited, $inviteCode);
        return $invite;
    }

    protected function buildQueryInvite(User $userInvited, Builder $query): Builder
    {
        $emailUserInvited = $userInvited->getEmailForVerification();
        $phoneUserInvited = $userInvited->phone_number;

        if ($emailUserInvited) {
            $query->where('email', $emailUserInvited);
        }

        if ($phoneUserInvited) {
            $query->where('phone_number', $phoneUserInvited);
        }

        return $query;
    }

    protected function handleCancelInvite(User $userInvited, InviteCode $inviteCode): void
    {
        $this->repository->getModel()->newQuery()
            ->where('email', $userInvited->getEmailForVerification())
            ->where('user_id', $inviteCode->userId())
            ->whereNot('invite_code', $inviteCode->code)
            ->update([
                'status_id' => Invite::INVITE_CANCELLED,
            ]);
    }
}
