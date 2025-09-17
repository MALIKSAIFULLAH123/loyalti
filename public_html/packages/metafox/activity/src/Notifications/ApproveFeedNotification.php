<?php

namespace MetaFox\Activity\Notifications;

use Illuminate\Support\Arr;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Notifications\ApproveNotification;
use MetaFox\User\Support\Facades\UserEntity;

class ApproveFeedNotification extends ApproveNotification
{
    protected string $type = 'activity_feed_approved';

    /**
     * @param  IsNotifiable $notifiable
     * @return MailMessage
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $service = new MailMessage();

        $subject = $this->localize('activity::mail.approved_feed_subject');

        $text = $this->localize('activity::mail.approved_feed_content');

        $url = '';
        if ($this->model instanceof HasUrl) {
            $url = $this->model->toUrl();
        }

        return $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($text)
            ->action($this->localize('core::phrase.view_now'), $url ?? '');
    }

    public function callbackMessage(): ?string
    {
        $data = $this->data;

        if (!is_array($data)) {
            return null;
        }

        $item = Arr::get($data, 'item');

        if (is_array($item)) {
            $ownerId = (int) Arr::get($item, 'owner_id', 0);

            if ($ownerId > 0) {
                $user = UserEntity::getById($ownerId)->detail;
                if ($user instanceof User) {
                    return $user->getApprovedMessage();
                }
            }
        }

        return $this->localize('activity::notification.an_admin_approved_your_post');
    }

    public function toLink(): ?string
    {
        $item = $this->model?->item;
        if ($item instanceof Content) {
            $owner = $item->owner;
            if (method_exists($owner, 'toDiscussionUrl')) {
                return $owner->toDiscussionUrl();
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
                return $owner->toRouter();
            }
        }

        return parent::toRouter();
    }
}
