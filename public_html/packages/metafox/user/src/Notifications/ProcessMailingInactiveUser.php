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
 * Class ProcessMailingInactiveUser.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 */
class ProcessMailingInactiveUser extends Notification
{
    use Queueable;

    protected string $type = 'process_mailing_inactive_user';

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
            ->subject($this->localize('user::mail.process_mailing_inactive_user_subject'))
            ->line($this->localize('user::mail.process_mailing_inactive_user_text', [
                'site_name' => Settings::get('core.general.site_name'),
                'site_url'  => config('app.url'),
            ]));
    }

    public function toArray(IsNotifiable $notifiable): array
    {
        return [];
    }

    public function callbackMessage(): ?string
    {
        return null;
    }
}
