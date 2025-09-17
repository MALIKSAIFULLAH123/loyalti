<?php

namespace MetaFox\Invite\Repositories\Eloquent;

use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use MetaFox\Invite\Jobs\SendInviteJob;
use MetaFox\Invite\Models\Invite;
use MetaFox\Invite\Models\InviteCode;
use MetaFox\Invite\Policies\InvitePolicy;
use MetaFox\Invite\Repositories\InviteCodeRepositoryInterface;
use MetaFox\Invite\Repositories\InviteRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Sms\Support\Traits\PhoneNumberTrait;
use MetaFox\User\Http\Resources\v1\User\UserItemCollection;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class InviteRepository.
 * @method Invite find($id, $columns = ['*'])
 */
class InviteRepository extends AbstractRepository implements InviteRepositoryInterface
{
    use UserMorphTrait;
    use PhoneNumberTrait;

    public function model()
    {
        return Invite::class;
    }

    protected function userRepository(): UserRepositoryInterface
    {
        return resolve(UserRepositoryInterface::class);
    }

    protected function codeRepository(): InviteCodeRepositoryInterface
    {
        return resolve(InviteCodeRepositoryInterface::class);
    }

    /**
     * @inheritDoc
     * @param User  $user
     * @param array $params
     * @return bool
     */
    public function createInvites(User $user, array $params): array
    {
        $recipients     = Arr::get($params, 'recipients');
        $message        = Arr::get($params, 'message');
        $expireSetting  = Settings::get('invite.invite_link_expire_days', 0);
        $checkDuplicate = Settings::get('invite.enable_check_duplicate_invite', true);
        $expireAt       = $expireSetting == 0 ? null : Carbon::now()->addDays($expireSetting);
        $arrayExists    = $arraySuccess = $arrayDuplicate = $idInvites = [];
        $inviteCode     = $this->codeRepository()->createCode($user, $expireAt);

        $data = [
            'user_id'     => $user->entityId(),
            'user_type'   => $user->entityType(),
            'expired_at'  => $expireAt,
            'message'     => $message,
            'invite_code' => $inviteCode->code,
        ];

        foreach ($recipients as $key => $item) {
            $item      = Str::lower($item);
            $userExist = $this->builderQueryUserByPhoneOrMail($item)->first();

            if ($userExist) {
                $arrayExists[] = $userExist;
                continue;
            }
            $query = $this->builderQueryInvite($user, $item, $checkDuplicate);

            if ($checkDuplicate && $query->exists()) {
                $arrayDuplicate[] = $item;
                continue;
            }

            $invite = $query->first();

            if ($invite instanceof Invite) {
                if ($invite->status_id == Invite::INVITE_PENDING) {
                    unset($recipients[$key]);
                    continue;
                }

                $invite->update([
                    'status_id'  => Invite::INVITE_PENDING,
                    'expired_at' => $expireAt,
                ]);
                $idInvites[] = $invite->entityId();
                continue;
            }

            $data   = $this->handleDataInsert($user, $data, $item);
            $invite = new Invite();
            $invite->fill($data)->save();

            $idInvites[]    = $invite->entityId();
            $arraySuccess[] = $item;
        }

        SendInviteJob::dispatch($idInvites);

        return [
            'communities' => empty($arrayExists) ? null : new UserItemCollection($arrayExists),
            'success'     => empty($arraySuccess) ? null : $arraySuccess,
            'duplicates'  => empty($arrayDuplicate) ? null : $arrayDuplicate,
            'recipients'  => [],
            'message'     => null,
        ];
    }

    public function getMessageForInviteSuccess(array $recipients): string
    {
        $totalRecipients = count($recipients);

        $message = __p('invite::phrase.invited_successfully_email_or_phone_only_recipient', [
            'recipient' => Str::lower($recipients[0]),
        ]);

        if ($totalRecipients > 2) {
            $message = __p('invite::phrase.invited_successfully_email_or_phone_n_recipient', [
                'recipient' => Str::lower($recipients[0]),
                'total'     => $totalRecipients - 1,
            ]);
        }

        if ($totalRecipients == 2) {
            $message = __p('invite::phrase.invited_successfully_email_or_phone_two_recipient', [
                'recipient_1' => Str::lower($recipients[0]),
                'recipient_2' => Str::lower($recipients[1]),
            ]);
        }

        return $message;
    }

    protected function handleDataInsert(User $user, array $data, string $recipient): array
    {
        $keyGenerate = sprintf(
            Invite::INVITE_KEY_FORMAT,
            $user->getEmailForVerification(),
            $recipient,
            $data['expired_at']
        );

        $data = array_merge($data, [
            'code'         => md5($keyGenerate),
            'phone_number' => null,
            'email'        => $recipient,
        ]);

        if ($this->validatePhoneNumber($recipient)) {
            Arr::set($data, 'phone_number', $recipient);
            Arr::set($data, 'email', null);
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function builderQueryInvite(User $user, string $value, bool $checkDuplicate = false): Builder
    {
        $query = $this->getModel()->newQuery()
            ->where(function (Builder $builder) use ($value) {
                $builder->where('email', $value)
                    ->orWhere('phone_number', $value);
            });

        if (!$checkDuplicate) {
            $query->where('user_id', $user->entityId());
        }

        return $query;
    }

    /**
     * @param string $value
     *
     * @return Builder
     */
    private function builderQueryUserByPhoneOrMail(string $value): Builder
    {
        $value = Str::lower($value);

        return $this->userRepository()->getModel()->newQuery()
            ->where(function (Builder $builder) use ($value) {
                $builder->where('users.email', $value)
                    ->orWhere('users.phone_number', $value);
            });
    }

    /**
     * @param User $user
     * @param int  $id
     *
     * @return Invite|null
     * @throws AuthorizationException
     */
    public function resend(User $user, int $id): ?Invite
    {
        $invite = $this->find($id);

        $expireSetting = Settings::get('invite.invite_link_expire_days', 0);

        $expireAt = $expireSetting == 0 ? null : Carbon::now()->addDays($expireSetting);

        $inviteCode = $this->getInviteCodeForResending($user, $invite, $expireAt);

        policy_authorize(InvitePolicy::class, 'update', $user, $invite);

        if ($invite->status_id == Invite::INVITE_COMPLETED) {
            return null;
        }

        $update = [
            'expired_at' => $expireAt,
        ];

        if ($invite->invite_code != $inviteCode->code) {
            Arr::set($update, 'invite_code', $inviteCode->code);
        }

        $invite->update($update);

        return $invite->refresh();
    }

    private function getInviteCodeForResending(User $user, Invite $invite, ?CarbonInterface $expiredAt): InviteCode
    {
        /**
         * @var InviteCode $currentInviteCode
         */
        $currentInviteCode = InviteCode::query()
            ->where([
                'user_id'   => $user->entityId(),
                'user_type' => $user->entityType(),
                'code'      => $invite->invite_code,
            ])
            ->first();

        /*
         * In case the invite code does not exist, we will create new one base on expired time
         */
        if (null === $currentInviteCode) {
            $currentInviteCode = resolve(InviteCodeRepositoryInterface::class)->createCode($user, $expiredAt);
        }

        /*
         * In case resending with no expired time
         */
        if (null === $expiredAt) {
            /*
             * If current invite code is also no expired time, we do not need to do any thing
             */
            if (null === $currentInviteCode->expired_at) {
                return $currentInviteCode;
            }

            /*
             * If current invite code has specific expired time, we must create new no expired time code
             */
            return resolve(InviteCodeRepositoryInterface::class)->createCode($user, $expiredAt);
        }

        /*
         * In case resending with specific expired time
         */

        /*
         * In case current invite code has no expired time, we must create new expired time code
         */
        if (null === $currentInviteCode->expired_at) {
            return resolve(InviteCodeRepositoryInterface::class)->createCode($user, $expiredAt);
        }

        /*
         * Last case is resending and current have specific expired time, we only need to update the expired time
         */
        $currentInviteCode->update([
            'expired_at' => $expiredAt,
        ]);

        return $currentInviteCode->refresh();
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function viewInvites(User $user, User $owner, array $params): Paginator
    {
        $limit = Arr::get($params, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        policy_authorize(InvitePolicy::class, 'viewAny', $user, $owner);

        $query = $this->getModel()->newQuery()->where('user_id', $owner->entityId());

        $query = $this->buildQueryViewInvites($user, $query, $params);

        return $query->orderByDesc('updated_at')->paginate($limit);
    }

    public function getBuilderByInviteCode(string $inviteCode): Builder
    {
        return $this->getModel()->newQuery()
            ->where('invite_code', $inviteCode);
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function batchResend(User $user, array $params): void
    {
        $ids = Arr::get($params, 'id', []);
        if (empty($ids)) {
            return;
        }

        foreach ($ids as $key => $id) {
            $result = $this->resend($user, $id);

            if (!$result instanceof Invite) {
                unset($ids[$key]);
            }
        }

        SendInviteJob::dispatch($ids);
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function batchDeleted(User $user, array $params): void
    {
        $ids = Arr::get($params, 'id', []);

        if (empty($ids)) {
            return;
        }

        foreach ($ids as $id) {
            $this->deleteInvite($user, $id);
        }
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function deleteInvite(User $user, int $id): bool
    {
        $invite = $this->find($id);

        policy_authorize(InvitePolicy::class, 'delete', $user, $invite);

        return $invite->delete();
    }

    public function getBuilderByCode(string $code): Builder
    {
        return $this->getModel()->newQuery()
            ->where([
                'code' => $code,
            ]);
    }

    public function buildQueryViewInvites(User $user, Builder $query, array $params): Builder
    {
        $search    = Arr::get($params, 'q');
        $status    = Arr::get($params, 'status');
        $startDate = Arr::get($params, 'start_date');
        $endDate   = Arr::get($params, 'end_date');
        $table     = $this->getModel()->getTable();

        if ($search) {
            $query->where(function (Builder $builder) use ($search, $table) {
                $builder->where("$table.email", $this->likeOperator(), '%' . $search . '%')
                    ->orWhere("$table.phone_number", $this->likeOperator(), '%' . $search . '%');
            });
        }

        if ($startDate) {
            $query->where("$table.created_at", '>=', $startDate);
        }

        if ($endDate) {
            $query->where("$table.created_at", '<=', $endDate);
        }

        switch ($status) {
            case Invite::STATUS_PENDING:
                $query->where("$table.status_id", Invite::INVITE_PENDING);
                break;
            case Invite::STATUS_COMPLETED:
                $query->where("$table.status_id", Invite::INVITE_COMPLETED);
                break;
            case Invite::STATUS_CANCELLED:
                $query->where("$table.status_id", Invite::INVITE_CANCELLED);
                break;
            default:
                break;
        }

        return $query;
    }
}
