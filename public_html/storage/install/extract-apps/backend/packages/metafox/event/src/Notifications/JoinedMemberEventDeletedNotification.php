<?php

namespace MetaFox\Event\Notifications;

use Illuminate\Support\Arr;
use MetaFox\Event\Models\Event as Model;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class JoinedMemberEventDeletedNotification.
 * @property Model $model
 * @ignore
 */
class JoinedMemberEventDeletedNotification extends Notification
{
    protected string $type = 'joined_member_event_delete';

    public function toArray(IsNotifiable $notifiable): array
    {
        return [
            'data'      => $this->data,
            'item_id'   => Arr::get($this->data, 'event_id'),
            'item_type' => 'event',
            'user_id'   => Arr::get($this->data, 'user_id'),
            'user_type' => Arr::get($this->data, 'user_type'),
        ];
    }

    public function callbackMessage(): ?string
    {
        return $this->localize('event::phrase.event_title_has_been_deleted', [
            'title' => Arr::get($this->data, 'event_name'),
        ]);
    }

    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $service = new MailMessage();

        $subject = $this->localize('event::phrase.event_has_been_deleted');

        $message = $this->localize('event::phrase.event_title_has_been_deleted', [
            'title' => Arr::get($this->data, 'title'),
        ]);

        return $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($message);
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl('/event?stab=going');
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl('/event?stab=going');
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileUrl('/event?stab=going');
    }
}
