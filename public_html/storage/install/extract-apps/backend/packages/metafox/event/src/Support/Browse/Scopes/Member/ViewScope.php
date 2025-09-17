<?php

namespace MetaFox\Event\Support\Browse\Scopes\Member;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Event\Models\Member;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 */
class ViewScope extends BaseScope
{
    public const VIEW_ALL        = 'all';
    public const VIEW_INTERESTED = 'interested';
    public const VIEW_JOINED     = 'joined';
    public const VIEW_HOST       = 'host';

    private int $eventId = 0;
    private int $userId  = 0;
    private string $view = self::VIEW_ALL;

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        return [
            self::VIEW_INTERESTED,
            self::VIEW_JOINED,
            self::VIEW_HOST,
        ];
    }

    /**
     * @return int
     */
    public function getEventId(): int
    {
        return $this->eventId;
    }

    /**
     * @param int $eventId
     *
     * @return ViewScope
     */
    public function setEventId(int $eventId): self
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * @return string
     */
    public function getView(): string
    {
        return $this->view;
    }

    /**
     * @param string $view
     *
     * @return ViewScope
     */
    public function setView(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Get the value of userId.
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Set the value of userId.
     *
     * @return self
     */
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $view    = $this->getView();
        $userId  = $this->getUserId();
        $eventId = $this->getEventId();

        if ($userId) {
            $builder->where('user_id', $userId);
        }

        if ($eventId) {
            $builder->where('event_id', $eventId);
        }

        switch ($view) {
            case self::VIEW_JOINED:
                $builder->where('rsvp_id', Member::JOINED);
                break;
            case self::VIEW_INTERESTED:
                $builder->where('rsvp_id', Member::INTERESTED);
                break;
            case self::VIEW_HOST:
                $builder->where('rsvp_id', Member::JOINED);
                $builder->where('role_id', Member::ROLE_HOST);
                break;
        }
    }
}
