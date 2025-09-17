<?php
namespace MetaFox\ActivityPoint\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Notifications\ApproveNotification;
use MetaFox\ActivityPoint\Models\ConversionRequest;

/**
 * @property ConversionRequest $model
 */
class PendingConversionRequestNotification extends ApproveNotification
{
    protected string $type = 'activitypoint_pending_conversion_request_notification';

    public function callbackMessage(): ?string
    {
        return $this->getMessage();
    }

    public function toMail(): ?MailMessage
    {
        $intro = $this->getMessage();

        $url   = $this->toUrl();

        return (new MailMessage())->line($intro)
            ->locale($this->getLocale())
            ->subject($this->localize('activitypoint::phrase.pending_point_conversion_request'))
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    private function getMessage(): string
    {
        $fullName = $this->model->user?->toTitle();

        if (null === $fullName) {
            return $this->localize('activitypoint::phrase.someone_created_point_conversion_request');
        }

        return $this->localize('activitypoint::phrase.pending_point_conversion_request_callback_message', [
            'full_name' => $fullName,
        ]);
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl('admincp/activitypoint/conversion-request/browse?id=' . $this->model->entityId());
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl('admincp/activitypoint/conversion-request/browse?id=' . $this->model->entityId());
    }

    public function toRouter(): ?string
    {
        return $this->toUrl();
    }
}
