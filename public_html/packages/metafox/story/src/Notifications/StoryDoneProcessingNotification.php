<?php

namespace MetaFox\Story\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\Story\Models\Story as Model;

/**
 * @property Model $model
 */
class StoryDoneProcessingNotification extends Notification
{
    protected string $type = 'story_video_done_processing';

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toMail($notifiable)
    {
        $mailService = new MailMessage();

        $emailLine = $this->localize('story::notification.your_story_video_is_ready');
        $url       = $this->model->toUrl();

        return $mailService
            ->locale($this->getLocale())
            ->subject($emailLine)
            ->line($emailLine)
            ->action($this->localize('core::phrase.view_now'), $url ?? '');
    }

    /**
     * @param IsNotifiable $notifiable
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
        return $this->localize('story::notification.your_story_video_is_ready');
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
