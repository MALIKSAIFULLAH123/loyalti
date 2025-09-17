<?php
namespace MetaFox\Featured\Notifications;

use MetaFox\Featured\Models\Item;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * @property Item $model
 */
class CancelledFeaturedItemForDeletedContentNotification extends Notification
{
    /**
     * @var string
     */
    protected string $type = 'featured_cancelled_item_for_deleted_content';

    /**
     * @param IsNotifiable $notifiable
     * @return array
     */
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

    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $intro = __p('featured::phrase.your_featured_item_has_been_cancelled_because_the_item_you_featured_has_been_deleted');

        $url   = $this->model->toUrl();

        return (new MailMessage())
            ->locale($this->getLocale())
            ->line($intro)
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    public function callbackMessage(): ?string
    {
        return __p('featured::phrase.your_featured_item_has_been_cancelled_because_the_item_you_featured_has_been_deleted');
    }
}
