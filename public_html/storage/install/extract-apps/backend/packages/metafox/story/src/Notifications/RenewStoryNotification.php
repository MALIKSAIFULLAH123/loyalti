<?php

namespace MetaFox\Story\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\Story\Models\Story as Model;

/**
 * @property Model $model
 */
class RenewStoryNotification extends Notification
{
    protected string $type = 'suggest_create_story';

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
        $emailLine   = $this->localize('story::notification.your_last_story_got_total_view_before_it_expired', [
            'total' => $this->model->total_view,
        ]);

        return $mailService
            ->locale($this->getLocale())
            ->subject($emailLine)
            ->line($emailLine)
            ->action($this->localize('story::phrase.create_story'), $this->toUrl() ?? '');
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
        if (!$this->model instanceof Model) {
            return null;
        }

        return $this->localize('story::notification.your_last_story_got_total_view_before_it_expired', [
            'total' => $this->model->total_view,
        ]);
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl("story/add");
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl("story/add");
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileUrl("story/add");
    }
}
