<?php

namespace MetaFox\Page\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Page\Models\Page;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

class ApproveNewPostNotification extends Notification
{
    protected string $type = 'page_new_post';

    /**
     * @inheritDoc
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
        $owner = $this->model->owner;
        $user  = $this->model->user;

        if (null === $owner) {
            return __p('page::notification.page_new_post_in_a_page_message', [
                'user_full_name' => $user->full_name,
            ]);
        }

        if (method_exists($this->model, 'toFollowerCallbackMessage') && $this->model->toFollowerCallbackMessage($this->getLocale())) {
            return $this->model->toFollowerCallbackMessage($this->getLocale());
        }

        if ($user instanceof Page) {
            return __p('page::notification.page_new_post_as_page', [
                'user_full_name' => $user->toTitle(),
            ]);
        }

        return __p('page::notification.page_new_post_in_a_page_title', [
            'user_full_name' => $user->full_name,
            'title'          => $owner->toTitle(),
        ]);
    }

    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $service = new MailMessage();
        $owner   = $this->model?->owner;
        $user    = $this->model?->user;
        $subject = $this->localize('page::mail.approved_new_post_in_a_page_subject');
        $url     = $this->model->toUrl() ?? '';
        $locale  = $this->getLocale();

        $text = $this->localize('page::mail.approved_new_post_in_page_message', [
            'user_full_name' => $user?->full_name,
            'title'          => $owner?->toTitle(),
        ]);

        if (null === $owner) {
            $text = $this->localize('page::mail.approved_new_post_in_a_page_message', [
                'user_full_name' => $user?->full_name,
            ]);
        }

        if ($user instanceof Page) {
            $text = $this->localize('page::mail.approved_new_post_in_a_page_message', [
                'user_full_name' => $owner?->toTitle(),
            ]);
        }

        if (method_exists($this->model, 'toFollowerCallbackMessage') && $this->model->toFollowerCallbackMessage($locale)) {
            $text = $this->model->toFollowerCallbackMessage($locale);
        }

        return $service
            ->locale($locale)
            ->subject($subject)
            ->line($text)
            ->action($this->localize('core::phrase.view_now'), $url);
    }
}
