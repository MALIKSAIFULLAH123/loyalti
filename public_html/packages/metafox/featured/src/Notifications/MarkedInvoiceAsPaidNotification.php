<?php
namespace MetaFox\Featured\Notifications;

use MetaFox\Featured\Models\Invoice;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Notifications\Notification;

/**
 * @property Invoice $model
 */
class MarkedInvoiceAsPaidNotification extends Notification
{
    protected string $type = 'featured_marked_invoice_as_paid';

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
        return __p('featured::phrase.your_invoice_has_been_marked_as_paid_by_the_administrator');
    }

    public function toMail(): MailMessage
    {
        $intro = __p('featured::phrase.your_invoice_has_been_marked_as_paid_by_the_administrator');

        $url   = $this->model->toUrl();

        return (new MailMessage())
            ->locale($this->getLocale())
            ->line($intro)
            ->action($this->localize('core::phrase.view_now'), $url);
    }
}
