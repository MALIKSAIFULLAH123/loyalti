<?php

namespace MetaFox\Like\Notifications;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Like\Models\Like;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\User\Models\UserEntity;
use MetaFox\User\Support\Facades\UserEntity as UserEntityFacade;

/**
 * Class LikeNotification.
 * @ignore
 * @codeCoverageIgnore
 * @property Like $model
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class LikeNotification extends Notification
{
    protected string $type = 'like_notification';

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

    public function toUrl(): ?string
    {
        $item = $this->model->item;

        if (!$item instanceof Content) {
            return null;
        }

        return $item->toUrl();
    }

    public function toLink(): ?string
    {
        $item = $this->model->item;

        if (!$item instanceof Content) {
            return null;
        }

        return $item->toLink();
    }

    public function toRouter(): ?string
    {
        $item = $this->model->item;

        if (!$item instanceof Content) {
            return null;
        }

        return $item->toRouter();
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param IsNotifiable $notifiable
     *
     * @return MailMessage
     */
    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $context        = UserEntityFacade::getById($notifiable->entityId())->detail;
        $item           = $this->model->item;
        $userEntity     = $this->model->userEntity;
        $friendFullName = $userEntity instanceof UserEntity ? $userEntity->name : null;
        /**
         * @var string|null $subject
         */
        $subject = app('events')->dispatch(
            'like.notification_to_callback_message',
            [$context, $userEntity, $item],
            true
        );

        if (!is_string($subject)) {
            // Default message in case no event data is returned
            $subject = $this->localize('like::phrase.user_reacted_to_your_post', [
                'user' => $friendFullName,
            ]);
        }

        // TODO: should use separated email phrase instead of using strip_tags
        $content = $subject;
        $subject = strip_tags($subject);
        $url     = $this->toUrl() ?? '';

        return (new MailMessage())
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($content)
            ->action($this->localize('like::phrase.view_this_reaction'), $url);
    }

    /**
     * @throws AuthenticationException
     */
    public function callbackMessage(): ?string
    {
        $item           = $this->model->item;
        $userEntity     = $this->model->userEntity;
        $friendFullName = $userEntity instanceof UserEntity ? $userEntity->name : null;
        /**
         * @var string|null $message
         */
        $message = app('events')->dispatch(
            'like.notification_to_callback_message',
            [$this->notifiable, $userEntity, $item],
            true
        );

        if (!is_string($message)) {
            // Default message in case no event data is returned
            $message = $this->localize('like::phrase.user_reacted_to_your_post', [
                'user' => $friendFullName,
            ]);
        }

        return $message;
    }
}
