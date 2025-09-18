<?php

namespace MetaFox\LiveStreaming\Notifications;

use Illuminate\Support\Str;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\LiveStreaming\Models\LiveVideo as Model;

/**
 * @property Model $model
 */
class StartLiveStreamNotification extends Notification
{
    protected string $type = 'start_livestream';

    public function toMail($notifiable)
    {
        $mailService = new MailMessage();

        $emailSubject = $this->localize('livestreaming::mail.user_is_live_now_subject', ['user' => $this->model->user?->toTitle()]);
        $emailLine    = $this->localize('livestreaming::phrase.user_is_live_now', ['user' => $this->model->user?->toTitle()]);
        $url          = $this->model->toUrl();

        return $mailService
            ->locale($this->getLocale())
            ->subject($emailSubject)
            ->line($emailLine)
            ->action($this->localize('core::phrase.view_now'), $url ?? '');
    }

    /**
     * @param  IsNotifiable         $notifiable
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
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
        return $this->localize('livestreaming::phrase.user_is_live_now', [
            'user' => Str::limit($this->model->user?->toTitle(), 40),
        ]);
    }

    public function toUrl(): ?string
    {
        return $this->model->toUrl();
    }

    public function toLink(): ?string
    {
        return $this->model->toLink();
    }

    public function toRouter(): ?string
    {
        return $this->model->toRouter();
    }
}
