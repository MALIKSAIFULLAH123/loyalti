<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Notifications;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\User;

/**
 * Class ApproveNotification.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ApproveNotification extends Notification
{
    /**
     * @var int
     */
    protected int $userId = 0;

    /**
     * @var string
     */
    protected string $userType = '';

    public function __construct($model = null)
    {
        parent::__construct($model);

        if ($model instanceof Content) {
            $this->userId = $this->model->userId();

            $this->userType = $this->model->userType();
        }
    }

    public function setContext(?User $user): self
    {
        if (!$user instanceof User) {
            return $this;
        }

        $this->userId = $user->entityId();

        $this->userType = $user->entityType();

        return $this;
    }

    public function toArray(IsNotifiable $notifiable): array
    {
        return [
            'data'      => $this->model->toArray(),
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $this->userId,
            'user_type' => $this->userType,
        ];
    }

    /**
     * Extended classes must implement this method.
     * @return string|null
     */
    public function callbackMessage(): ?string
    {
        return null;
    }
}
