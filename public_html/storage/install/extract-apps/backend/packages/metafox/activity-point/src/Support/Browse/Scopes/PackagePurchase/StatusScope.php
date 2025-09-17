<?php

namespace MetaFox\ActivityPoint\Support\Browse\Scopes\PackagePurchase;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Models\PackagePurchase;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class StatusScope extends BaseScope
{
    public const STATUS_ALL              = 'all';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_COMPLETED        = 'completed';
    public const STATUS_FAILED           = 'failed';
    protected string $status = self::STATUS_ALL;

    public const STATUS_MAP_TEXT = [
        'payment::phrase.status_pending_approval' => PackagePurchase::STATUS_INIT,
        'payment::phrase.status_completed'        => PackagePurchase::STATUS_SUCCESS,
        'payment::phrase.status_failed'           => PackagePurchase::STATUS_FAILED,
    ];

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return array<int, string>
     */
    public static function getAllowStatus(): array
    {
        return Arr::pluck(self::getStatusOptions(), 'value');
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function getStatusOptions(): array
    {
        return [
            [
                'label' => __p('core::phrase.all'),
                'value' => self::STATUS_ALL,
            ],
            [
                'label' => __p('payment::phrase.status_pending_approval'),
                'value' => self::STATUS_PENDING_APPROVAL,
            ],
            [
                'label' => __p('payment::phrase.status_completed'),
                'value' => self::STATUS_COMPLETED,
            ],
            [
                'label' => __p('payment::phrase.status_failed'),
                'value' => self::STATUS_FAILED,
            ],
        ];
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model): void
    {
        $table  = $model->getTable();
        $status = $this->getStatus();

        switch ($status) {
            case self::STATUS_PENDING_APPROVAL:
                $builder->where("$table.status", PackagePurchase::STATUS_INIT);
                break;
            case self::STATUS_COMPLETED:
                $builder->where("$table.status", PackagePurchase::STATUS_SUCCESS);
                break;
            case self::STATUS_FAILED:
                $builder->where("$table.status", PackagePurchase::STATUS_FAILED);
                break;
        }
    }
}
