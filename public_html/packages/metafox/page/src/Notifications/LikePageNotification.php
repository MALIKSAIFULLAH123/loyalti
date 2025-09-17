<?php

namespace MetaFox\Page\Notifications;

use Illuminate\Bus\Queueable;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageMember;
use MetaFox\Page\Models\PageMember as Model;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\User\Models\UserEntity;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class LikePageNotification.
 * @property Model $model
 * @ignore
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class LikePageNotification extends Notification
{
    use Queueable;

    protected string $type = 'like_page';

    /**
     * Get the mail representation of the notification.
     *
     * @param  IsNotifiable $notifiable
     * @return MailMessage
     */
    public function toMail(IsNotifiable $notifiable)
    {
        [$pageTitle, $userFullName] = $this->getParams();

        $subject = $this->localize('page::mail.user_full_name_like_page_subject', [
            'user_full_name' => $userFullName,
            'title'          => $pageTitle,
        ]);

        $content = $this->localize('page::notification.user_full_name_like_page_title', [
            'user_full_name' => $userFullName,
            'title'          => $pageTitle,
        ]);

        return (new MailMessage())
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($content)
            ->action($this->localize('page::phrase.view_page'), $this->model->page->toUrl());
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  IsNotifiable         $notifiable
     * @return array<string, mixed>
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
        $params = $this->getParams();
        if (empty($params)) {
            return null;
        }

        [$pageTitle, $userFullName] = $params;

        return $this->localize('page::notification.user_full_name_like_page_title', [
            'user_full_name' => $userFullName,
            'title'          => $pageTitle,
        ]);
    }

    /**
     * @return array<int, mixed>
     */
    protected function getParams(): array
    {
        if (!$this->model instanceof PageMember) {
            return [];
        }

        $userEntity = $this->model->userEntity;
        $page       = $this->model->page;

        $userFullName = null;
        $pageTitle    = null;

        if ($userEntity instanceof UserEntity) {
            $userFullName = $userEntity->name;
        }

        if ($page instanceof Page) {
            $pageTitle = ban_word()->clean($page->name);
        }

        return [$pageTitle, $userFullName];
    }

    public function toLink(): ?string
    {
        return $this->model?->page?->toLink();
    }

    public function toRouter(): ?string
    {
        $page = $this->model?->page;
        if (!$page instanceof Page) {
            return null;
        }

        return $page->toRouter();
    }

    public function toUrl(): ?string
    {
        $page = $this->model?->page;
        if (!$page instanceof Page) {
            return null;
        }

        return $page->toUrl();
    }
}
