<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Notifications\Notification as LaravelNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\NotificationManagerInterface;
use MetaFox\Platform\Contracts\UserEntity;
use MetaFox\Sms\Support\Message;
use MetaFox\User\Models\User;

/**
 * Class Notification.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
abstract class Notification extends LaravelNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Indicates notification type.
     *
     * @var string
     */
    protected string $type;

    /** @var mixed */
    protected mixed $model;

    protected ?UserEntity $user = null;

    /** @var array<mixed> */
    protected array $data = [];

    /** @var NotificationManagerInterface */
    protected NotificationManagerInterface $manager;

    protected ?IsNotifiable $notifiable = null;

    /**
     * Notification constructor.
     *
     * @param mixed $model
     */
    public function __construct($model = null)
    {
        $this->model = $model;
    }

    /**
     * Get notification type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param IsNotifiable $notifiable
     *
     * @return string[]
     */
    public function via(IsNotifiable $notifiable): array
    {
        return resolve(NotificationManagerInterface::class)
            ->getChannelsForNotifiableByType($notifiable, $this->getType());
    }

    /**
     * Must return an array to store to database.
     *
     * @param IsNotifiable $notifiable
     *
     * @return array<mixed>
     */
    abstract public function toArray(IsNotifiable $notifiable): array;

    /**
     * Set model for notification.
     * @param  Entity $item
     * @return void
     */
    public function setModel(Entity $item): void
    {
        $this->model = $item;
    }

    /**
     * Assign value of data column in table notifications.
     * @param  array<int, mixed> $data
     * @return void
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function setUser(?UserEntity $user): void
    {
        $this->user = $user;
    }

    abstract public function callbackMessage(): ?string;

    public function toUrl(): ?string
    {
        $resource = $this->model;

        if (!$resource instanceof HasUrl) {
            return null;
        }

        return $resource->toUrl();
    }

    public function toRouter(): ?string
    {
        $resource = $this->model;

        if (!$resource instanceof HasUrl) {
            return null;
        }

        return $resource->toRouter();
    }

    public function toLink(): ?string
    {
        $resource = $this->model;

        if (!$resource instanceof HasUrl) {
            return null;
        }

        return $resource->toLink();
    }

    /**
     * @param  IsNotifiable         $notifiable
     * @return array<string, mixed>
     */
    public function toMobileMessage(IsNotifiable $notifiable): array
    {
        $message = $this->callbackMessage() ?? '';

        return [
            'message' => strip_tag_content($message),
            'url'     => $this->toUrl(),
            'router'  => $this->toRouter(),
        ];
    }

    /**
     * @param IsNotifiable $notifiable
     *
     * @return ?Message
     */
    public function toTextMessage(IsNotifiable $notifiable): ?Message
    {
        $content = $this->callbackMessage() ?? '';
        if (empty($content)) {
            return null;
        }

        /** @var Message $message */
        $message = resolve(Message::class);
        $message->setContent($content);
        $message->setUrl($this->toUrl());

        return $message;
    }

    public function setNotifiable(?IsNotifiable $notifiable): self
    {
        $this->notifiable = $notifiable;

        return $this;
    }

    /**
     * @param  string|null          $key
     * @param  array<string, mixed> $replace
     * @return string
     */
    public function localize(string $key = null, array $replace = []): string
    {
        $target    = $this->notifiable instanceof User ? $this->notifiable : null;
        $canAccess = $target && method_exists($target, 'hasPermissionTo') && $target->hasPermissionTo('admincp.has_admin_access');

        if ($canAccess) {
            Config::set('localize.view_mode', config('localize.display_translation_key', false) ? 'edit' : 'view');
        }

        return __p($key, $replace, $this->getLocale());
    }

    public function getLocale(): ?string
    {
        if ($this->notifiable instanceof HasLocalePreference) {
            return $this->notifiable->preferredLocale();
        }

        return null;
    }
}
