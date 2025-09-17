<?php

namespace MetaFox\Importer\Support\Browse\Scopes\Bundle;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class StatusScope extends BaseScope
{
    public const STATUS_DONE = 'done';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_PENDING = 'pending';

    public const STATUS_FAILED = 'failed';

    /**
     * @return array<int, string>
     */
    public static function getAllowStatus(): array
    {
        return Arr::pluck(self::getStatusOptions(), 'value');
    }

    /**
     * @return array<string>
     */
    public static function getStatusOptions(): array
    {
        return [
            [
                'label' => __p('importer::phrase.done'),
                'value' => self::STATUS_DONE,
            ],
            [
                'label' => __p('importer::phrase.processing'),
                'value' => self::STATUS_PROCESSING,
            ],
            [
                'label' => __p('importer::phrase.pending'),
                'value' => self::STATUS_PENDING,
            ],
            [
                'label' => __p('importer::phrase.failed'),
                'value' => self::STATUS_FAILED,
            ],
        ];
    }

    /**
     * @var string
     */
    private string $status = Browse::VIEW_ALL;

    /**
     * @param  string  $status
     * @return StatusScope
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param  Builder  $builder
     * @param  Model    $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $status = $this->getStatus();
        $table = $model->getTable();

        switch ($status) {
            case self::STATUS_DONE:
                $builder->where($this->alias($table, 'status'), self::STATUS_DONE);
                break;
            case self::STATUS_PROCESSING:
                $builder->where($this->alias($table, 'status'), self::STATUS_PROCESSING);
                break;
            case self::STATUS_PENDING:
                $builder->where($this->alias($table, 'status'), self::STATUS_PENDING);
                break;
            case self::STATUS_FAILED:
                $builder->where($this->alias($table, 'status'), self::STATUS_FAILED);
                break;
        }
    }
}
