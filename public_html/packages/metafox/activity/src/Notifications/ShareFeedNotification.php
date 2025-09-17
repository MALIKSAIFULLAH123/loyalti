<?php

namespace MetaFox\Activity\Notifications;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Activity\Models\Feed;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Notifications\Notification;

/**
 * @property Feed $model
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ShareFeedNotification extends Notification
{
    protected string $type = 'activity_share_notification';

    /**
     * @param  IsNotifiable         $notifiable
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray(IsNotifiable $notifiable): array
    {
        return [
            'data'      => $this->model->toArray(),
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $this->model->userId(),
            'user_type' => $this->model->userType(),
        ];
    }

    /**
     * @param  IsNotifiable $notifiable
     * @return MailMessage
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $service    = new MailMessage();
        $item       = $this->model?->item;
        $owner      = $this->model->owner;
        $userEntity = $this->model->userEntity;
        $title      = null;

        if ($item instanceof HasTitle) {
            $title = $this->handleTitle($item, $item?->toTitle());
        }

        $hasTitle = is_string($title) && MetaFoxConstant::EMPTY_STRING != $title;

        $url = $this->toUrl() ?? '';
        /* @var string|null $ownerType */
        $ownerType = $owner->hasNamedNotification();

        $subject = $this->localize('activity::mail.share_feed_subject', ['user' => $userEntity->name]);

        $text = $this->localize('activity::notification.user_share_your_post', [
            'user'    => $userEntity->name,
            'title'   => $title,
            'isTitle' => (int) !empty($hasTitle),
        ]);

        if ($ownerType != null) {
            $text = $this->localize('activity::mail.user_share_your_post_in_name', [
                'user'       => $userEntity->name,
                'title'      => $title,
                'isTitle'    => (int) !empty($hasTitle),
                'owner_type' => __p_type_key($ownerType, [], $this->getLocale()),
                'owner_name' => $this->model->ownerEntity->name,
            ]);
        }

        return $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($text)
            ->action($this->localize('comment::phrase.view_this_comment'), $url);
    }

    public function callbackMessage(): ?string
    {
        $item  = $this->model?->item;
        $owner = $this->model->owner;
        $title = null;

        if ($item instanceof HasTitle) {
            $title = $this->handleTitle($item, $item?->toTitle());
        }

        /* @var string|null $ownerType */
        $ownerType = $owner->hasNamedNotification();

        $userEntity = $this->model->userEntity;

        $hasTitle = is_string($title) && MetaFoxConstant::EMPTY_STRING != $title;

        if ($ownerType != null) {
            return $this->localize('activity::notification.user_share_your_post_in_name', [
                'user'       => $userEntity->name,
                'title'      => $title,
                'isTitle'    => (int) $hasTitle,
                'owner_type' => __p_type_key($ownerType, [], $this->getLocale()),
                'owner_name' => $this->model->ownerEntity->name,
            ]);
        }

        if ($item instanceof HasPrivacyMember) {
            return $this->localize('activity::notification.user_share_your_page', [
                'user'      => $userEntity->name,
                'title'     => $title,
                'item_type' => __p_type_key($item->entityType(), [], $this->getLocale()),
            ]);
        }

        return $this->localize('activity::notification.user_share_your_post', [
            'user'    => $userEntity->name,
            'title'   => $title,
            'isTitle' => (int) $hasTitle,
        ]);
    }

    /**
     * @throws AuthenticationException
     */
    public function toLink(): ?string
    {
        return $this->model->toLink();
    }

    /**
     * @throws AuthenticationException
     */
    public function toRouter(): ?string
    {
        return $this->model->toRouter();
    }

    private function handleTitle(Content $item, string $title): string
    {
        app('events')->dispatch('core.parse_content', [$item, &$title]);

        return strip_tags(ban_word()->clean($title));
    }
}
