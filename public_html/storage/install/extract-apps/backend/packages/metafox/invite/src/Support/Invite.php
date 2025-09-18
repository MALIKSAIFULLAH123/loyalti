<?php

namespace MetaFox\Invite\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use MetaFox\Core\Mails\Mailable;
use MetaFox\Invite\Contracts\Invite as ContractsInvite;
use MetaFox\Invite\Models\Invite as ModelInvite;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Sms\Contracts\ManagerInterface;
use MetaFox\Sms\Support\Message;

/**
 * Class Invite.
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Invite implements ContractsInvite
{
    public const COMPLETE_COLOR  = '#31a24a';
    public const PENDING_COLOR   = '#f4b400';
    public const CANCELLED_COLOR = '#f02848';

    public function sendMail(Model $model): void
    {
        if (!$model instanceof ModelInvite) {
            return;
        }

        $url = $model->toLinkInvite();

        Mail::to($model->email)
            ->send(new Mailable([
                'subject' => __p('invite::mail.user_invites_you_to_site_name', [
                    'user_name' => $model->user->full_name,
                    'site_name' => config('app.name'),
                ]),
                'line'    => __p('invite::mail.invite_email_html', [
                    'user_name'   => $model->user->full_name,
                    'url'         => $url,
                    'site_name'   => config('app.name'),
                    'message'     => $model->message,
                    'invite_code' => $model->invite_code,
                    'has_setting' => 1,
                    'has_message' => (int) !empty($model->message),
                ]),
                'user'    => $model->user,
            ]));
    }

    /**
     * @inheritDoc
     */
    public function sendSMS(Model $model): void
    {
        if (!$model instanceof ModelInvite) {
            return;
        }

        $url = $model->toLinkInvite();

        $content = __p('invite::mail.invite_sms_message', [
            'user_name'   => $model->user->full_name,
            'url'         => $url,
            'site_name'   => config('app.name'),
            'message'     => $model->message,
            'invite_code' => $model->invite_code,
            'has_setting' => 1,
            'has_message' => (int) !empty($model->message),
        ]);

        if (empty($model->phone_number)) {
            return;
        }

        /** @var Message $message */
        $message = resolve(Message::class);
        $message->setContent($content);
        $message->setRecipients($model->phone_number);
        $message->setUrl($url);

        /** @var ManagerInterface $manager */
        $manager = resolve(ManagerInterface::class);
        $manager->service()->send($message);
    }

    /**
     * @inheritDoc
     */
    public function send(Model $model): void
    {
        if (!$model instanceof ModelInvite) {
            return;
        }
        if ($model->email == null) {
            $this->sendSMS($model);

            return;
        }

        $this->sendMail($model);
    }

    public function getStatusOptions(): array
    {
        return [
            ['value' => Browse::VIEW_ALL, 'label' => __p('core::phrase.when.all')],
            ['value' => ModelInvite::STATUS_PENDING, 'label' => __p('invite::phrase.pending')],
            ['value' => ModelInvite::STATUS_COMPLETED, 'label' => __p('invite::phrase.completed')],
            ['value' => ModelInvite::STATUS_CANCELLED, 'label' => __p('invite::phrase.cancelled')],
        ];
    }

    public function getStatusPhrase(int $statusId): string
    {
        $data = [
            ModelInvite::INVITE_PENDING   => __p('invite::phrase.pending'),
            ModelInvite::INVITE_COMPLETED => __p('invite::phrase.completed'),
            ModelInvite::INVITE_CANCELLED => __p('invite::phrase.cancelled'),
        ];

        return $data[$statusId];
    }

    public function getStatusInfo(int $statusId): array
    {
        $data = [
            ModelInvite::INVITE_PENDING   => [
                'label' => __p('invite::phrase.pending'),
                'color' => self::PENDING_COLOR,
            ],
            ModelInvite::INVITE_COMPLETED => [
                'label' => __p('invite::phrase.completed'),
                'color' => self::COMPLETE_COLOR,
            ],
            ModelInvite::INVITE_CANCELLED => [
                'label' => __p('invite::phrase.cancelled'),
                'color' => self::CANCELLED_COLOR,
            ],
        ];

        return $data[$statusId];
    }

    public function getStatusRules(): array
    {
        return [
            Browse::VIEW_ALL,
            ModelInvite::STATUS_PENDING,
            ModelInvite::STATUS_COMPLETED,
            ModelInvite::STATUS_CANCELLED,
        ];
    }
}
