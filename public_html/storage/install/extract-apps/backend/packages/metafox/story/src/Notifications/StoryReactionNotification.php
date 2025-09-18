<?php

namespace MetaFox\Story\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\Story\Models\StoryReaction as Model;
use MetaFox\User\Models\UserEntity;

/**
 * @property Model $model
 */
class StoryReactionNotification extends Notification
{
    protected string $type = 'story_reaction';

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toMail($notifiable)
    {
        $mailService  = new MailMessage();
        $userEntity   = $this->model->userEntity;
        $userFullName = $userEntity instanceof UserEntity ? $userEntity->name : null;
        $emailLine    = $this->localize('story::notification.user_reaction_your_story', [
            'user_name' => $userFullName,
        ]);
        $subject      = $this->localize('story::notification.user_reaction_your_story_subject', [
            'user_name' => $userFullName,
        ]);

        return $mailService
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($emailLine)
            ->action($this->localize('core::phrase.view_now'), $this->toUrl() ?? '');
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

        $userEntity   = $this->model?->userEntity;
        $userFullName = $userEntity instanceof UserEntity ? $userEntity->name : null;

        return $this->localize('story::notification.user_reaction_your_story', [
            'user_name' => $userFullName,
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
