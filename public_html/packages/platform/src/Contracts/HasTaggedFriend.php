<?php

namespace MetaFox\Platform\Contracts;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\User\Models\UserEntity;

/**
 * Interface HasTaggedFriend.
 * @property int $total_tag_friend
 */
interface HasTaggedFriend extends Entity
{
    /**
     * @param  MailMessage     $service
     * @param  UserEntity|null $user
     * @param  UserEntity|null $owner
     * @param  bool            $isMention
     * @return MailMessage
     */
    public function toMail(MailMessage $service, ?UserEntity $user, ?UserEntity $owner, bool $isMention = false): MailMessage;

    /**
     * @param  UserEntity $user
     * @param  UserEntity $owner
     * @param  bool       $isMention
     * @return string
     */
    public function toCallbackMessage(UserEntity $user, UserEntity $owner, bool $isMention = false): string;

    /**
     * @return string|null
     */
    public function toTagFriendUrl(): ?string;

    /**
     * @return string|null
     */
    public function toTagFriendLink(): ?string;

    /**
     * @return string|null
     */
    public function toTagFriendRouter(): ?string;

    /**
     * @return bool
     */
    public function hasTagStream(): bool;

    /**
     * @param  int   $isReview
     * @return mixed
     */
    public function setIsReview(int $isReview);

    /**
     * @return int
     */
    public function isReview(): int;

    /**
     * @param  IsNotifiable $notifiable
     * @return void
     */
    public function setTagFriendNotifiable(IsNotifiable $notifiable): void;

    /**
     * @return IsNotifiable|null
     */
    public function getTagFriendNotifiable(): ?IsNotifiable;
}
