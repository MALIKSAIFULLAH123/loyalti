<?php

namespace MetaFox\Comment\Notifications;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Comment\Models\Comment;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\UserEntity;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\User\Support\Facades\UserEntity as UserEntityFacade;

/**
 * @property Comment $model
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class CommentNotification extends Notification
{
    protected string $type = 'new_comment';

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
        $resource       = $this->model;
        $parentResource = $resource?->parentComment;
        $item           = $resource?->item;
        $userEntity     = $resource?->userEntity;
        $friendFullName = $userEntity instanceof UserEntity ? $userEntity->name : null;
        $url            = $this->toUrl() ?? '';

        if (null !== $parentResource && $parentResource->userId() == $context->entityId()) {
            $replacements = [
                'user' => $friendFullName,
            ];

            $subject = $this->localize('comment::phrase.mail.user_replied_on_your_comment_subject', $replacements);
            $line = $this->localize('comment::phrase.mail.user_replied_on_your_comment', $replacements);

            return (new MailMessage())
                ->locale($this->getLocale())
                ->subject($subject)
                ->line($line)
                ->action($this->localize('comment::phrase.view_this_comment'), $url);
        }

        /**
         * @var string|null $subject
         */
        $subject = app('events')->dispatch(
            'comment.notification_to_callback_message',
            [$context, $userEntity, $item],
            true
        );

        if (!is_string($subject)) {
            // Default message in case no event data is returned
            $subject = $this->localize('comment::notification.user_commented_on_your_post', [
                'user' => $friendFullName,
            ]);
        }

        // TODO: should use separated email phrase instead of using strip_tags
        $content = $subject;
        $subject = strip_tags($subject);

        return (new MailMessage())
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($content)
            ->action($this->localize('comment::phrase.view_this_comment'), $url);
    }

    /**
     * @throws AuthenticationException
     */
    public function callbackMessage(): ?string
    {
        $context        = $this->notifiable;
        $resource       = $this->model;
        $parentResource = $resource?->parentComment;
        $item           = $resource?->item;
        $userEntity     = $resource?->userEntity;
        $friendFullName = $userEntity instanceof UserEntity ? $userEntity->name : null;

        if (null !== $parentResource && $parentResource->userId() == $context?->entityId()) {
            return $this->localize('comment::phrase.mail.user_replied_on_your_comment', [
                'user' => $friendFullName,
            ]);
        }

        /**
         * @var string|null $message
         */
        $message = app('events')->dispatch(
            'comment.notification_to_callback_message',
            [$context, $userEntity, $item],
            true
        );

        if (!is_string($message)) {
            // Default message in case no event data is returned
            $message = $this->localize('comment::notification.user_commented_on_your_post', [
                'user' => $friendFullName,
            ]);
        }

        return $message;
    }

    private function handleTitle(Content $item, string $title): string
    {
        app('events')->dispatch('core.parse_content', [$item, &$title]);

        $title = strip_tags($title);

        return $title;
    }

    public function toMobileMessage(IsNotifiable $notifiable): array
    {
        $resource       = $this->model;
        $parentResource = $resource?->parentComment;
        $item           = $resource?->item;
        $userEntity     = $resource?->userEntity;
        $friendFullName = $userEntity instanceof UserEntity ? $userEntity->name : null;
        $message        = null;

        if (null !== $parentResource && $notifiable->entityId() == $parentResource->userId()) {
            $message = $this->localize('comment::phrase.mail.user_replied_on_your_comment', [
                'user' => $friendFullName,
            ]);
        }

        if (null === $message) {
            /**
             * @var string|null $message
             */
            $message = app('events')->dispatch(
                'comment.notification_to_callback_message',
                [$notifiable, $userEntity, $item],
                true
            );

            if (!is_string($message)) {
                // Default message in case no event data is returned
                $message = $this->localize('comment::notification.user_commented_on_your_post', [
                    'user' => $friendFullName,
                ]);
            }
        }

        return [
            'message' => strip_tag_content($message),
            'url'     => $this->toUrl(),
            'router'  => $this->toRouter(),
        ];
    }
}
