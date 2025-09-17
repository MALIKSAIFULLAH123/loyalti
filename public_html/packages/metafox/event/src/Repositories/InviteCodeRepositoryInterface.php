<?php

namespace MetaFox\Event\Repositories;

use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\InviteCode;
use MetaFox\Event\Models\Member;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface InviteCode.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface InviteCodeRepositoryInterface
{
    /**
     * getCode.
     *
     * @param User  $user
     * @param Event $event
     * @param  ?int $active
     * @return InviteCode
     */
    public function getCode(User $user, Event $event, ?int $active = null): ?InviteCode;

    /**
     * getCodeByValue.
     *
     * @param string $codeValue
     * @param  ?int  $active
     * @return InviteCode
     */
    public function getCodeByValue(string $codeValue, ?int $active = null): ?InviteCode;

    /**
     * createCode.
     *
     * @param User  $user
     * @param Event $event
     * @param int   $active
     * @return InviteCode
     */
    public function createCode(User $user, Event $event, int $active = InviteCode::STATUS_ACTIVE): InviteCode;

    /**
     * generateUniqueCodeValue.
     *
     * @return string
     */
    public function generateUniqueCodeValue(): string;

    /**
     * refreshCode.
     *
     * @param User  $user
     * @param Event $event
     * @return InviteCode
     */
    public function refreshCode(User $user, Event $event): InviteCode;

    /**
     * getActiveCode.
     *
     * @param User  $user
     * @param Event $event
     * @return InviteCode
     */
    public function generateCode(User $user, Event $event): InviteCode;

    /**
     * verifyCodeByValue.
     *
     * @param string $codeValue
     * @return ?InviteCode
     */
    public function verifyCodeByValue(string $codeValue): ?InviteCode;

    /**
     * verifyCodeByValueAndContext.
     *
     * @param User   $context
     * @param Event  $event
     * @param string $codeValue
     * @return ?InviteCode
     */
    public function verifyCodeByValueAndContext(User $context, Event $event, string $codeValue): ?InviteCode;

    /**
     * acceptCodeByValue.
     *
     * @param User   $context
     * @param string $codeValue
     * @return ?Member
     */
    public function acceptCodeByValue(User $context, string $codeValue): ?Member;
}
