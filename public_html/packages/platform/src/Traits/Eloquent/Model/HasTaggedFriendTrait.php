<?php

namespace MetaFox\Platform\Traits\Eloquent\Model;

use Hamcrest\Core\IsNot;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasTaggedFriend;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\User\Models\UserEntity;

/**
 * @mixin HasTaggedFriend
 * @mixin Content
 */
trait HasTaggedFriendTrait
{
    protected int $isReview;

    public function toMail(MailMessage $service, ?UserEntity $user, ?UserEntity $owner, bool $isMention = false): MailMessage
    {
        $friendName = $owner instanceof UserEntity ? $owner->name : null;
        $yourName   = $user instanceof UserEntity ? $user->name : null;

        $emailTitle = __p($isMention ? 'core::phrase.username_mentioned_you_in_a_post_subject' : 'core::phrase.username_tagged_you_in_a_post_subject', [
            'username' => $yourName,
            'item'     => 'post',
        ]);

        $emailLine = __p($isMention ? 'core::phrase.hi_friend_username_mentioned_you_in_a_post' : 'core::phrase.hi_friend_username_tagged_you_in_a_post', [
            'friend'   => $friendName,
            'username' => $yourName,
            'item'     => 'post',
        ]);

        $url = $this->toUrl();

        if ($this->activity_feed != null) {
            $url = $this->activity_feed->toUrl();
        }

        return $service
            ->subject($emailTitle)
            ->line($emailLine)
            ->action(__p('core::phrase.review_now'), $url ?? '');
    }

    /**
     * @param  UserEntity $user
     * @param  UserEntity $owner
     * @param  bool       $isMention
     * @return string
     */
    public function toCallbackMessage(UserEntity $user, UserEntity $owner, bool $isMention = false): string
    {
        $yourName = $user->name;
        $owner    = $owner->detail;

        if ($owner instanceof HasPrivacyMember) {
            return __p($isMention ? 'core::phrase.username_mentioned_entity_type_title_in_a_post_review_now' : 'core::phrase.username_tagged_entity_type_title_in_a_post_review_now', [
                'username'     => $yourName,
                'entity_type'  => $owner->entityType(),
                'entity_title' => $owner->toTitle(),
                'is_review'    => $this->isReview(),
            ]);
        }

        return __p($isMention ? 'core::phrase.username_mentioned_you_in_a_post_review_now' : 'core::phrase.username_tagged_you_in_a_post_review_now', [
            'username'  => $yourName,
            'is_review' => $this->isReview(),
        ]);
    }

    /**
     * @return string|null
     */
    public function toTagFriendUrl(): ?string
    {
        if ($this->activity_feed instanceof Content) {
            return $this->activity_feed->toUrl();
        }

        return $this->toUrl();
    }

    /**
     * @return string|null
     */
    public function toTagFriendLink(): ?string
    {
        if ($this->activity_feed instanceof Content) {
            return $this->activity_feed->toLink();
        }

        return $this->toLink();
    }

    /**
     * @return string|null
     */
    public function toTagFriendRouter(): ?string
    {
        if ($this->activity_feed instanceof Content) {
            return $this->activity_feed->toRouter();
        }

        return $this->toRouter();
    }

    public function hasTagStream(): bool
    {
        return true;
    }

    public function setIsReview(int $isReview)
    {
        return $this->isReview = $isReview;
    }

    public function isReview(): int
    {
        return $this->isReview;
    }

    /**
     * @inheritDoc
     */
    public function setTagFriendNotifiable(IsNotifiable $notifiable): void
    {
        if (!$this instanceof Model) {
            return;
        }

        $this->setAttribute('tagged_friend_notifiable', $notifiable);
    }

    public function getTagFriendNotifiable(): ?IsNotifiable
    {
        if (!$this instanceof Model) {
            return null;
        }

        $notifiable = $this->getAttribute('tagged_friend_notifiable');

        return $notifiable instanceof IsNotifiable ? $notifiable : null;
    }
}
