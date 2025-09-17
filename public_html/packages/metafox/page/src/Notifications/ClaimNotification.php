<?php

namespace MetaFox\Page\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Page\Models\PageClaim as Model;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\User\Models\UserEntity;

/**
 * Class ClaimNotification.
 * @property Model $model
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ClaimNotification extends Notification
{
    protected string $type = 'claim_page';

    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $service = new MailMessage();

        $userEntity = $this->model->userEntity;

        $userFullName = $userEntity instanceof UserEntity ? $userEntity->name : null;
        $adminName    = $notifiable->notificationFullName();

        $emailTitle = $this->localize('page::phrase.you_have_a_request_claim_page_subject', ['user_name' => $userFullName]);
        $emailLine  = $this->localize('page::phrase.hi_your_name_you_have_a_request_claim_page_from_username', [
            'user_name'  => $userFullName,
            'admin_name' => $adminName,
        ]);

        return $service
            ->locale($this->getLocale())
            ->subject($emailTitle)
            ->line($emailLine)
            ->action(
                $this->localize('page::phrase.view_page'),
                $this->model->page->toUrl()
            );
    }

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
        $userEntity = $this->model->userEntity;

        $fromUserName = $userEntity instanceof UserEntity ? $userEntity->name : null;

        return $this->localize('page::notification.you_have_a_request_claim_page', ['user_name' => $fromUserName]);
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl('admincp/page/claim/browse');
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl('admincp/page/claim/browse');
    }
}
