<?php
namespace MetaFox\Featured\Notifications;

use MetaFox\Featured\Models\Invoice;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\Platform\Contracts\User;

/**
 * @property Invoice $model
 */
class CancelledInvoiceNotification extends Notification
{
    /**
     * @var string
     */
    protected string $type = 'featured_cancelled_invoice';

    /**
     * @var User|null
     */
    protected ?User $sender = null;

    /**
     * @param User $sender
     * @return $this
     */
    public function setSender(User $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function toArray(IsNotifiable $notifiable): array
    {
        $userId = $this->model->user_id;

        $userType = $this->model->user_type;

        if ($this->sender instanceof User) {
            $userId = $this->sender->entityId();

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

    public function callbackMessage(): ?string
    {
        return __p('featured::phrase.your_invoice_has_been_cancelled_by_the_administrator');
    }

    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $intro = __p('featured::phrase.your_invoice_has_been_cancelled_by_the_administrator');

        $url   = $this->model->toUrl();

        return (new MailMessage())
            ->locale($this->getLocale())
            ->line($intro)
            ->action($this->localize('core::phrase.view_now'), $url);
    }
}
