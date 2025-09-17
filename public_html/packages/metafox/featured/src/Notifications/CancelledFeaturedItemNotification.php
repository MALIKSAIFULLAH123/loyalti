<?php
namespace MetaFox\Featured\Notifications;

use MetaFox\Featured\Models\Item;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\Platform\Contracts\User;

/**
 * @property Item $model
 */
class CancelledFeaturedItemNotification extends Notification
{
    /**
     * @var string
     */
    protected string $type = 'featured_cancelled_item';

    /**
     * @var User|null
     */
    protected ?User $sender = null;

    /**
     * @param User $sender
     * @return self
     */
    public function setSender(User $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @param IsNotifiable $notifiable
     * @return array
     */
    public function toArray(IsNotifiable $notifiable): array
    {
        $userId = $this->model->user_id;

        $userType = $this->model->user_type;

        if ($this->sender instanceof User) {
            $userId   = $this->sender->entityId();

            $userType = $this->sender->entityType();
        }

        return [
            'data'      => $this->model->toArray(),
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $userId,
            'user_type' => $userType,
        ];
    }

    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $intro = __p('featured::phrase.your_featured_item_has_been_cancelled_by_the_administrator');

        $url   = $this->model->toUrl();

        return (new MailMessage())
            ->locale($this->getLocale())
            ->line($intro)
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    public function callbackMessage(): ?string
    {
        return __p('featured::phrase.your_featured_item_has_been_cancelled_by_the_administrator');
    }
}
