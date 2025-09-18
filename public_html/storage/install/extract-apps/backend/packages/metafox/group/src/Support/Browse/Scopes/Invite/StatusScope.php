<?php

namespace MetaFox\Group\Support\Browse\Scopes\Invite;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use MetaFox\Group\Models\Invite;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class StatusScope extends BaseScope
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_ACCEPTED  = 'accepted';
    public const STATUS_DENIED    = 'denied';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED   = 'expired';

    /**
     * @return array<int, string>
     */
    public static function getAllowStatus(): array
    {
        return array_keys(self::getStatusLabelMap());
    }

    /**
     * @return array
     */
    public static function getStatusOptions(): array
    {
        $options = [];
        foreach (self::getStatusLabelMap() as $value => $label) {
            $options[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return $options;
    }

    public static function getStatusLabelMap(): array
    {
        return [
            self::STATUS_PENDING   => __p('core::phrase.pending'),
            self::STATUS_ACCEPTED  => __p('group::phrase.accepted'),
            self::STATUS_DENIED    => __p('group::phrase.decline'),
            self::STATUS_CANCELLED => __p('core::phrase.cancelled'),
            self::STATUS_EXPIRED   => __p('core::web.expired'),
        ];
    }

    /**
     * @var string
     */
    private string $status = self::STATUS_PENDING;

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $status = $this->getStatus();
        $table  = $model->getTable();

        switch ($status) {
            case self::STATUS_PENDING:
                $builder->where($this->alias($table, 'status_id'), Invite::STATUS_PENDING);
                break;
            case self::STATUS_DENIED:
                $builder->whereIn($this->alias($table, 'status_id'), [Invite::STATUS_NOT_USE, Invite::STATUS_NOT_INVITE_AGAIN]);
                break;
            case self::STATUS_ACCEPTED:
                $builder->where($this->alias($table, 'status_id'), Invite::STATUS_APPROVED);
                break;
            case self::STATUS_CANCELLED:
                $builder->where($this->alias($table, 'status_id'), Invite::STATUS_CANCELLED);
                break;
            case self::STATUS_EXPIRED:
                $builder->whereIn($this->alias($table, 'status_id'), [Invite::STATUS_PENDING, Invite::STATUS_EXPIRED])
                    ->where($this->alias($table, 'expired_at'), '<=', Carbon::now()->toDateTimeString());
                break;
        }
    }
}
