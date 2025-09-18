<?php

namespace MetaFox\Page\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageClaim as Model;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * Class ApproveRequestClaimNotification.
 * @property Model $model
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ApproveRequestClaimNotification extends Notification
{
    protected string $type = 'approve_claim_page';
    /**
     * @var int|null
     */
    protected ?int $userId = null;

    /**
     * @var string|null
     */
    protected ?string $userType = null;

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUserId(): int
    {
        if ($this->userId == null) {
            $this->userId = $this->model->userId();
        }

        return $this->userId;
    }

    public function setUserType(string $userType): self
    {
        $this->userType = $userType;

        return $this;
    }

    public function getUserType(): string
    {
        if ($this->userType == null) {
            $this->userType = $this->model->userType();
        }

        return $this->userType;
    }

    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $page    = $this->model->page;
        $title   = $page instanceof Page ? $page->toTitle() : null;
        $context = $this->notifiable;
        $subject = $this->localize('page::mail.your_have_now_become_the_new_owner_of_title_subject', ['title' => $title]);
        $content = $this->localize('page::mail.your_have_now_become_the_new_owner_of_title', ['title' => $title]);

        if ($page->userId() != $context?->entityId()) {
            $content = $this->localize(
                'page::mail.your_have_been_removed_as_owner_of_page_title',
                ['title' => $title]
            );
            $subject = $this->localize('page::mail.your_have_been_removed_as_owner_of_page_title_subject', ['title' => $title]);
        }

        return (new MailMessage())
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($content)
            ->action($this->localize('page::phrase.view_page'), $this->model->page->toUrl());
    }

    public function toArray(IsNotifiable $notifiable): array
    {
        return [
            'data'      => $this->model->toArray(),
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $this->getUserId(),
            'user_type' => $this->getUserType(),
        ];
    }

    public function callbackMessage(): ?string
    {
        $page    = $this->model->page;
        $title   = $page instanceof Page ? $page->toTitle() : null;
        $context = $this->notifiable;

        if ($page->userId() != $context?->entityId()) {
            return $this->localize(
                'page::notification.your_have_been_removed_as_owner_of_page_title',
                ['title' => $title]
            );
        }

        return $this->localize('page::notification.your_have_now_become_the_new_owner_of_title', ['title' => $title]);
    }
}
