<?php

namespace MetaFox\User\Notifications;

use Illuminate\Bus\Queueable;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Notifications\Notification;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class WelcomeNewMember.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 */
class WelcomeNewMember extends Notification
{
    use Queueable;

    protected string $type = 'user_welcome';

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed         $notifiable
     * @return array<string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed                                          $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->locale($this->getLocale())
            ->subject($this->localize('user::mail.new_member_welcome_subject', ['site' => Settings::get('core.general.site_name')]))
            ->line($this->localize('user::mail.new_member_welcome_content'));
    }

    public function toArray(IsNotifiable $notifiable): array
    {
        return [];
    }

    public function callbackMessage(): ?string
    {
        // TODO: Implement callbackMessage() method.
        return null;
    }
}
