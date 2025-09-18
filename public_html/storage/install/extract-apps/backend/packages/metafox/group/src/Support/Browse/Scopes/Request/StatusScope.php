<?php

namespace MetaFox\Group\Support\Browse\Scopes\Request;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Models\Request;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class StatusScope extends BaseScope
{
    public const STATUS_PENDING  = Request::STATUS_PENDING;
    public const STATUS_APPROVED = Request::STATUS_APPROVED;
    public const STATUS_DENIED   = Request::STATUS_DENIED;
    public const STATUS_CANCEL   = Request::STATUS_CANCEL;

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
            self::STATUS_PENDING  => __p('group::phrase.item_status.pending'),
            self::STATUS_APPROVED => __p('group::phrase.item_status.approved'),
            self::STATUS_DENIED   => __p('group::phrase.item_status.declined'),
            self::STATUS_CANCEL   => __p('group::phrase.item_status.cancelled'),
        ];
    }

    /**
     * @var int
     */
    private int $status = self::STATUS_PENDING;

    /**
     * @param  int         $status
     * @return StatusScope
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
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
                $builder->where($this->alias($table, 'status_id'), self::STATUS_PENDING);
                break;
            case self::STATUS_DENIED:
                $builder->where($this->alias($table, 'status_id'), self::STATUS_DENIED);
                break;
            case self::STATUS_APPROVED:
                $builder->where($this->alias($table, 'status_id'), self::STATUS_APPROVED);
                break;
            case self::STATUS_CANCEL:
                $builder->where($this->alias($table, 'status_id'), self::STATUS_CANCEL);
                break;
        }
    }
}
