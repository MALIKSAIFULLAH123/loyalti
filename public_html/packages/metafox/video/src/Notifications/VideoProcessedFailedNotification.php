<?php

namespace MetaFox\Video\Notifications;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\Video\Models\Video as Model;

/**
 * @property Model $model
 */
class VideoProcessedFailedNotification extends Notification
{
    protected string $type = 'video_processed_failed';

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return MailMessage
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toMail($notifiable)
    {
        $mailService  = new MailMessage();
        $title        = Str::limit($this->model->toTitle(), 40);
        $emailSubject = $this->localize('video::phrase.your_video_is_failed_subject', [
            'title'     => $title,
            'has_title' => (int) !empty($title),
        ]);
        $emailLine    = $this->localize('video::phrase.your_video_is_failed', [
            'title'     => $title,
            'has_title' => (int) !empty($title),
        ]);
        $url          = $this->model->toUrl();

        return $mailService
            ->locale($this->getLocale())
            ->subject($emailSubject)
            ->line($emailLine)
            ->action($this->localize('core::phrase.view_now'), $url ?? '');
    }

    /**
     * @param IsNotifiable $notifiable
     *
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
        $title = $this->model->toTitle() ?? $this->localize(Model::VIDEO_DEFAULT_TITLE_PHRASE);
        $title = parse_input()->clean(Str::limit($title, 40));

        return $this->localize('video::phrase.your_video_is_failed_subject', [
            'title'     => $title,
            'has_title' => (int) !empty($title),
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
