<?php

namespace MetaFox\Page\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageInvite as Model;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\User\Models\UserEntity;

/**
 * Class PageInvite.
 *
 * @property Model $model
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PageInvite extends Notification
{
    protected string $type = 'page_invite';

    /**
     * Get the mail representation of the notification.
     *
     * @param IsNotifiable $notifiable
     *
     * @return MailMessage
     */
    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $service = new MailMessage();

        $ownerEntity = $this->model->ownerEntity;
        $userEntity  = $this->model->userEntity;
        $page        = $this->model->page;

        $ownerFullName = $ownerEntity instanceof UserEntity ? $ownerEntity->name : null;
        $userFullName  = $userEntity instanceof UserEntity ? $userEntity->name : null;

        $pageTitle = null;
        $url       = '#';
        if ($page instanceof Page) {
            $pageTitle = ban_word()->clean($page->name);
            /** @var string $url */
            $url = $this->model->toUrl();
        }

        $emailTitle = $this->localize('page::phrase.you_have_a_page_invitation_from_full_name_mail_title', [
            'full_name' => $userFullName,
        ]);

        $emailLine = match ($this->model->invite_type) {
            Model::INVITE_ADMIN => $this->localize('page::notification.user_full_name_invited_you_to_admin_page_title', [
                'user_full_name' => $userFullName,
                'title'          => $pageTitle,
            ]),
            Model::INVITE_MEMBER => $this->localize('page::phrase.hi_owner_full_name_user_full_name_invited_you_to_like_page_title', [
                'owner_full_name' => $ownerFullName,
                'user_full_name'  => $userFullName,
                'title'           => $pageTitle,
            ]),
        };

        return $service
            ->locale($this->getLocale())
            ->subject($emailTitle)
            ->line($emailLine)
            ->action($this->localize('core::phrase.view_now'), $url);
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
        [$pageTitle, $userFullName] = $this->getParams();

        if ($this->model->invite_type == Model::INVITE_ADMIN) {
            return $this->localize('page::notification.user_full_name_invited_you_to_admin_page_title', [
                'user_full_name' => $userFullName,
                'title'          => $pageTitle,
            ]);
        }

        return $this->localize('page::notification.user_full_name_invited_you_to_like_page_title', [
            'user_full_name' => $userFullName,
            'title'          => $pageTitle,
        ]);
    }

    protected function getParams(): array
    {
        $userEntity = $this->model->userEntity;
        $page       = $this->model->page;

        $userFullName = null;
        $pageTitle    = null;

        if ($userEntity instanceof UserEntity) {
            $userFullName = $userEntity->name;
        }

        if ($page instanceof User) {
            $pageTitle = ban_word()->clean($page->name);
        }

        return [$pageTitle, $userFullName];
    }

    public function toLink(): ?string
    {
        return $this->model->page->toLink();
    }
}
