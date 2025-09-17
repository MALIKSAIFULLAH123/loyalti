<?php

namespace MetaFox\Event\Support;

use MetaFox\Event\Contracts\EventMembershipContract;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\Member;
use MetaFox\Event\Repositories\MemberRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class EventMembership implements EventMembershipContract
{
    /**
     * @return MemberRepositoryInterface
     */
    public function memberRepository(): MemberRepositoryInterface
    {
        return resolve(MemberRepositoryInterface::class);
    }

    /**
     * @param  Event  $event
     * @param  User   $user
     *
     * @return int
     */
    public function getMembership(Event $event, User $user): int
    {
        $row = $this->memberRepository()->getMemberRow($event->entityId(), $user->entityId());

        return $row ? $row->rsvp_id : 0;
    }

    /**
     * @return array<int>
     */
    public function getAllowRoleOptions(): array
    {
        return [
            Member::ROLE_MEMBER,
            Member::ROLE_HOST,
        ];
    }

    /**
     * @return array<int>
     */
    public function getAllowRsvpOptions(): array
    {
        return [
            Member::NOT_INTERESTED,
            Member::JOINED,
            Member::INTERESTED,
        ];
    }

    /**
     * @inheritDoc
     */
    public function parseRsvp(): array
    {
        return [
            Member::NOT_INTERESTED => __p('event::phrase.not_interested'),
            Member::JOINED         => __p('event::phrase.going'),
            Member::INTERESTED     => __p('event::phrase.interested'),
        ];
    }
}
