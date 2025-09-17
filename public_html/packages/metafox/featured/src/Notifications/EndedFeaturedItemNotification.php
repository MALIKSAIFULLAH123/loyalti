<?php
namespace MetaFox\Featured\Notifications;

use MetaFox\Featured\Models\Item;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * @property Item $model
 */
class EndedFeaturedItemNotification extends Notification
{
    /**
     * @var string
     */
    protected string $type = 'featured_ended_item';

    public function toArray(IsNotifiable $notifiable): array
    {
        return [
            'data'      => $this->model->toArray(),
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $this->model->user_id,
            'user_type' => $this->model->user_type,
        ];
    }

    public function callbackMessage(): ?string
    {
        return __p('featured::phrase.your_featured_item_has_ended');
    }

    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $intro = __p('featured::phrase.your_featured_item_has_ended');

        $url   = $this->model->toUrl();

        return (new MailMessage())
            ->locale($this->getLocale())
            ->line($intro)
            ->action($this->localize('core::phrase.view_now'), $url);
    }
}
