<?php

namespace MetaFox\Notification\Channels;

use Closure;
use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Markdown;
use Illuminate\Mail\Message;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Notification\Support\TypeManager;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\User\Models\User as UserModels;
use MetaFox\User\Support\Facades\User as UserFacade;

class MailChannel
{
    /**
     * The mailer implementation.
     *
     * @var MailFactory
     */
    protected $mailer;

    /**
     * The markdown implementation.
     *
     * @var Markdown
     */
    protected $markdown;

    /**
     * Create a new mail channel instance.
     *
     * @param MailFactory $mailer
     * @param Markdown    $markdown
     *
     * @return void
     */
    public function __construct(MailFactory $mailer, Markdown $markdown)
    {
        $this->mailer   = $mailer;
        $this->markdown = $markdown;
    }

    /**
     * Send the given notification.
     *
     * @param       $notifiable
     * @param       $notification
     *
     * @return void
     */
    public function send($notifiable, $notification)
    {
        if (!$notifiable instanceof IsNotifiable) {
            // avoid other notifiable errors?, etc: Spatie\Backup\Notifications\Notifiable
            return null;
        }

        if (!$notification instanceof Notification) {
            return null;
        }

        if (!$this->canSend($notifiable, $notification)) {
            return;
        }

        $notification->setNotifiable($notifiable);
        $message = null;

        if (method_exists($notification, 'toMail')) {
            $message = $notification->toMail($notifiable);
        }

        if (
            !$notifiable->routeNotificationFor('mail', $notification) &&
            !$message instanceof Mailable
        ) {
            return;
        }

        if ($message instanceof Mailable) {
            $message->send($this->mailer);

            return;
        }

        if (!$message instanceof MailMessage) {
            return;
        }

        $message->theme('metafox');

        $this->addVariables($notifiable, $message);

        $mailer = $message->mailer ?? null;

        $this->mailer->mailer($mailer)->send(
            $this->buildView($message),
            array_merge($message->data(), $this->additionalMessageData($notification)),
            $this->messageBuilder($notifiable, $notification, $message)
        );
    }

    /**
     * @param IsNotifiable $notifiable
     * @param MailMessage  $message
     *
     * @return void
     */
    protected function addVariables(IsNotifiable $notifiable, MailMessage $message): void
    {
        $message->variables([
            'recipient_full_name'  => $notifiable->full_name,
            'recipient_first_name' => $notifiable->first_name,
            'recipient_last_name'  => $notifiable->last_name,
        ]);
    }

    /**
     * Get the mailer Closure for the message.
     *
     * @param IsNotifiable $notifiable
     * @param Notification $notification
     * @param MailMessage  $message
     *
     * @return Closure
     */
    protected function messageBuilder(IsNotifiable $notifiable, Notification $notification, MailMessage $message)
    {
        return function ($mailMessage) use ($notifiable, $notification, $message) {
            $this->buildMessage($mailMessage, $notifiable, $notification, $message);
        };
    }

    /**
     * Build the notification's view.
     *
     * @param MailMessage $message
     *
     * @return string|array<mixed>
     */
    protected function buildView(MailMessage $message)
    {
        if ($message->view) {
            return $message->view;
        }

        if (property_exists($message, 'theme') && null !== $message->theme) {
            $this->markdown->theme($message->theme);
        }

        if ($message->markdown == null) {
            abort(400, 'Empty markdown');
        }

        return [
            'html' => $this->markdown->render($message->markdown, $message->data()),
            'text' => $this->markdown->renderText($message->markdown, $message->data()),
        ];
    }

    /**
     * Get additional meta-data to pass along with the view data.
     *
     * @param Notification $notification
     *
     * @return array<string, mixed>
     */
    protected function additionalMessageData(Notification $notification)
    {
        $implements = class_implements($notification);

        $queued = [];

        if ($implements !== false) {
            $queued = in_array(ShouldQueue::class, $implements);
        }

        return [
            '__laravel_notification_id'     => $notification->id,
            '__laravel_notification'        => get_class($notification),
            '__laravel_notification_queued' => $queued,
        ];
    }

    /**
     * Build the mail message.
     *
     * @param Message      $mailMessage
     * @param IsNotifiable $notifiable
     * @param Notification $notification
     * @param MailMessage  $message
     *
     * @return void
     */
    protected function buildMessage(
        Message      $mailMessage,
        IsNotifiable $notifiable,
        Notification $notification,
        MailMessage  $message
    )
    {
        $this->addressMessage($mailMessage, $notifiable, $notification, $message);

        $mailMessage->subject($message->subject
            ?: Str::title(
                Str::snake(class_basename($notification), ' ')
            ));

        $this->addAttachments($mailMessage, $message);

        if (is_int($message->priority)) {
            $mailMessage->setPriority($message->priority);
        }

        $this->runCallbacks($mailMessage, $message);
    }

    /**
     * Address the mail message.
     *
     * @param Message      $mailMessage
     * @param IsNotifiable $notifiable
     * @param Notification $notification
     * @param MailMessage  $message
     *
     * @return void
     */
    protected function addressMessage(
        Message      $mailMessage,
        IsNotifiable $notifiable,
        Notification $notification,
        MailMessage  $message
    )
    {
        $this->addSender($mailMessage, $message);

        $mailMessage->to($this->getRecipients($notifiable, $notification));

        if (!empty($message->cc)) {
            foreach ($message->cc as $cc) {
                $mailMessage->cc($cc[0], Arr::get($cc, 1));
            }
        }

        if (!empty($message->bcc)) {
            foreach ($message->bcc as $bcc) {
                $mailMessage->bcc($bcc[0], Arr::get($bcc, 1));
            }
        }
    }

    /**
     * Add the "from" and "reply to" addresses to the message.
     *
     * @param Message     $mailMessage
     * @param MailMessage $message
     *
     * @return void
     */
    protected function addSender(Message $mailMessage, MailMessage $message)
    {
        if (!empty($message->from)) {
            $mailMessage->from($message->from[0], Arr::get($message->from, 1));
        }

        if (!empty($message->replyTo)) {
            foreach ($message->replyTo as $replyTo) {
                $mailMessage->replyTo($replyTo[0], Arr::get($replyTo, 1));
            }
        }
    }

    /**
     * Get the recipients of the given message.
     *
     * @param IsNotifiable $notifiable
     * @param Notification $notification
     *
     * @return mixed
     */
    protected function getRecipients(IsNotifiable $notifiable, Notification $notification)
    {
        $recipients = $notifiable->routeNotificationFor('mail', $notification);

        if (is_string($recipients)) {
            $recipients = [$recipients];
        }

        return collect($recipients)->mapWithKeys(function ($recipient, $email) {
            return is_numeric($email)
                ? [$email => (is_string($recipient) ? $recipient : $recipient->email)]
                : [$email => $recipient];
        })->all();
    }

    /**
     * Add the attachments to the message.
     *
     * @param Message     $mailMessage
     * @param MailMessage $message
     *
     * @return void
     */
    protected function addAttachments(Message $mailMessage, MailMessage $message)
    {
        foreach ($message->attachments as $attachment) {
            $mailMessage->attach($attachment['file'], $attachment['options']);
        }

        foreach ($message->rawAttachments as $attachment) {
            $mailMessage->attachData($attachment['data'], $attachment['name'], $attachment['options']);
        }
    }

    /**
     * Run the callbacks for the message.
     *
     * @param Message     $mailMessage
     * @param MailMessage $message
     *
     * @return MailChannel
     */
    protected function runCallbacks(Message $mailMessage, MailMessage $message)
    {
        foreach ($message->callbacks as $callback) {
            $callback($mailMessage->getSwiftMessage());
        }

        return $this;
    }

    public function configMethodsCallbackMessage(): array
    {
        return [
            'toMail',
        ];
    }

    public function validateConfiguration(): bool
    {
        return (bool) app('mail')->validateConfiguration();
    }

    public function canSend(IsNotifiable $notifiable, Notification $notification): bool
    {
        $systemTypes = resolve(TypeManager::class)->getSystemTypes();
        if (!$notifiable instanceof UserModels) {
            return false;
        }

        if (empty($notifiable->notificationEmail())) {
            return false;
        }

        if (UserFacade::isBan($notifiable->entityId()) && !in_array($notification->getType(), $systemTypes)) {
            return false;
        }

        if (!$notifiable->hasVerified()) {
            return false;
        }

        if (!$notifiable->hasVerifiedEmail()) {
            return false;
        }

        return true;
    }
}
