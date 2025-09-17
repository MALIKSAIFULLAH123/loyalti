<?php

namespace MetaFox\Event\Repositories\Eloquent;

use Carbon\Carbon;
use Illuminate\Support\Str;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\InviteCode;
use MetaFox\Event\Models\Member;
use MetaFox\Event\Policies\EventPolicy;
use MetaFox\Event\Repositories\InviteCodeRepositoryInterface;
use MetaFox\Event\Repositories\MemberRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class InviteCodeRepository.
 */
class InviteCodeRepository extends AbstractRepository implements InviteCodeRepositoryInterface
{

    public function model()
    {
        return InviteCode::class;
    }

    public function memberRepository(): MemberRepositoryInterface
    {
        return resolve(MemberRepositoryInterface::class);
    }

    public function getCode(User $user, Event $event, ?int $active = null): ?InviteCode
    {
        $query = $this->getModel()->newQuery()
            ->where('user_id', $user->entityId())
            ->where('user_type', $user->entityType())
            ->where('event_id', $event->entityId());

        if (isset($active)) {
            $query->where('status', $active);
        }

        return $query->first();
    }

    public function getCodeByValue(string $codeValue, ?int $active = null): ?InviteCode
    {
        $query = $this->getModel()->newQuery()
            ->where('code', $codeValue);

        if (isset($active)) {
            $query->where('status', $active);
        }

        return $query->first();
    }

    public function createCode(User $user, Event $event, int $active = InviteCode::STATUS_ACTIVE): InviteCode
    {
        $numberHours = Settings::get('event.number_hours_expiration_invite_code', 0);
        $expiredAt   = null;

        if ($numberHours > 0) {
            $expiredAt = Carbon::now()->addHours($numberHours);
        }
        $code = new InviteCode([
            'user_id'    => $user->entityId(),
            'user_type'  => $user->entityType(),
            'event_id'   => $event->entityId(),
            'status_id'  => $active,
            'code'       => $this->generateUniqueCodeValue(),
            'expired_at' => $expiredAt,
        ]);

        $code->save();

        return $code;
    }

    public function generateUniqueCodeValue(): string
    {
        do {
            $code = Str::random(8);
        } while (InviteCode::where('code', $code)->first());

        return $code;
    }

    public function refreshCode(User $user, Event $event): InviteCode
    {
        policy_authorize(EventPolicy::class, 'invite', $user, $event);

        $activeCode = $this->getCode($user, $event, InviteCode::STATUS_ACTIVE);
        if ($activeCode) {
            $activeCode->status = 0;
            $activeCode->save();
        }

        return $this->createCode($user, $event, InviteCode::STATUS_ACTIVE);
    }

    public function generateCode(User $user, Event $event): InviteCode
    {
        policy_authorize(EventPolicy::class, 'invite', $user, $event);

        $activeCode = $this->getCode($user, $event, InviteCode::STATUS_ACTIVE);
        if ($activeCode) {
            return $activeCode;
        }

        return $this->createCode($user, $event, InviteCode::STATUS_ACTIVE);
    }

    public function verifyCodeByValue(string $codeValue): ?InviteCode
    {
        $code = $this->getCodeByValue($codeValue, InviteCode::STATUS_ACTIVE);
        if (!$code) {
            return null;
        }

        $user  = $code->user;
        $event = $code->event;

        policy_authorize(EventPolicy::class, 'invite', $user, $event);

        return $code;
    }

    public function verifyCodeByValueAndContext(User $context, Event $event, string $codeValue): ?InviteCode
    {
        $code = $this->verifyCodeByValue($codeValue);
        if (!$code) {
            return null;
        }

        if ($code->event_id != $event->entityId()) {
            return null;
        }

        if ($code->userId() == $context->entityId()) {
            // is not valid to the inviter himself
            return null;
        }

        return $code;
    }

    public function acceptCodeByValue(User $context, string $codeValue): ?Member
    {
        $code = $this->verifyCodeByValue($codeValue);
        if (!$code) {
            return null;
        }

        $event = $code->event;
        if (!$event) {
            return null;
        }

        return $this->memberRepository()->joinEvent($event, $context);
    }
}
