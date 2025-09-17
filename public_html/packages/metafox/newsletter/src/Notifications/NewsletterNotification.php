<?php

namespace MetaFox\Newsletter\Notifications;

use Illuminate\Bus\Queueable;
use MetaFox\Newsletter\Models\Newsletter as Model;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Notification\Repositories\TypeRepositoryInterface;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\Sms\Support\Message;
use MetaFox\User\Support\Facades\UserEntity as UserEntityFacade;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class ProcessMailingInactiveUser.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @property Model $model
 *
 * @ignore
 */
class NewsletterNotification extends Notification
{
    use Queueable;

    protected string $type = 'newsletter_notification';

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array<string>
     */
    public function via($notifiable): array
    {
        $newsletter = $this->model;

        if ($newsletter && $newsletter->override_privacy) {
            /**
             * @var TypeRepositoryInterface $repository
             */
            $repository       = resolve(TypeRepositoryInterface::class);
            $notificationType = $repository->getNotificationTypeByType($this->type);

            $notificationTypeChannels = $notificationType->typeChannels()
                ->where('is_active', 1)
                ->get()
                ->pluck('channel')
                ->toArray();
            $channels                 = array_merge(array_keys($newsletter->channels), $notificationTypeChannels);
            $channels                 = array_unique($channels);
            return array_values($channels);
        }

        return parent::via($notifiable);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $user           = UserEntityFacade::getById($notifiable->entityId())?->detail;
        $newsletter     = $this->model;
        $newsletterText = $newsletter?->newsletterText;

        return (new MailMessage())
            ->greeting($this->localize('newsletter::mail.newsletter_greeting', ['name' => $user?->full_name]))
            ->locale($this->getLocale())
            ->subject($this->localize($newsletter?->subject_raw))
            ->line($this->localize($newsletterText?->text_html_raw));
    }

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

    public function callbackMessage(): ?string
    {
        $newsletter     = $this->model;
        $newsletterText = $newsletter?->newsletterText;
        $text           = $newsletterText?->text_raw;
        $message        = $this->localize($text);

        if (empty($message)) {
            $message = $this->localize($newsletterText?->text_html_raw);
        }

        return $message;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     */
    public function toTextMessage($notifiable): ?Message
    {
        $newsletter     = $this->model;
        $newsletterText = $newsletter?->newsletterText;
        $content        = $newsletterText?->text_raw ?? '';

        if (empty($content)) {
            return null;
        }

        /** @var Message $message */
        $message = resolve(Message::class);
        $message->setContent($this->localize($content));
        $message->setUrl($this->toUrl());

        return $message;
    }
}
