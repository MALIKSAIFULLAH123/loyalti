<?php

namespace MetaFox\Activity\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Notifications\Notification;

class PendingFeedNotification extends Notification
{
    protected string $type = 'activity_feed_pending';

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
        $service = new MailMessage();

        $subject = $this->localize('activity::mail.pending_feed_subject');

        $text = $this->localize('activity::mail.pending_feed_content');

        $url = url_utility()->makeApiFullUrl($this->toLink());

        return $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($text)
            ->action($this->localize('core::phrase.view_now'), $url ?? '');
    }

    public function callbackMessage(): ?string
    {
        $item = $this->model?->item;
        if ($item instanceof Content) {
            $owner = $item->owner;
            if ($owner instanceof User) {
                return $this->localize(
                    'activity::notification.pending_post_in_entity_name_is_waiting_for_your_approval',
                    [
                        'entity_type'  => __p_type_key($item->ownerType(), [], $this->getLocale()),
                        'entity_title' => $owner->toTitle(),
                    ]
                );
            }
        }

        return $this->localize('activity::notification.pending_post_is_waiting_for_your_approval');
    }

    public function toLink(): ?string
    {
        $item = $this->model?->item;

        if ($item instanceof Content) {
            $owner = $item->owner;

            if (null !== $owner) {
                if (method_exists($owner, 'toManagePostUrl')) {
                    return $owner->toManagePostUrl();
                }
            }
        }

        return parent::toLink();
    }

    public function toUrl(): ?string
    {
        $link = $this->toLink();

        return $link ? url_utility()->makeApiFullUrl($link) : null;
    }

    public function toRouter(): ?string
    {
        $item = $this->model?->item;

        if ($item instanceof Content) {
            $owner = $item->owner;

            if (null !== $owner) {
                if (method_exists($owner, 'toManagePostRouter')) {
                    return $owner->toManagePostRouter();
                }
            }
        }

        return parent::toRouter();
    }
}
