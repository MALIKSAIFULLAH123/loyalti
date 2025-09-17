<?php

namespace MetaFox\Event\Support\Browse\Scopes\Event;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 */
class OnlineScope extends BaseScope
{
    private ?int $isOnline = null;

    /**
     * Set the value of isOnline.
     *
     * @param  int  $isOnline
     * @return self
     */
    public function setIsOnline(int $isOnline)
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    /**
     * Get the value of isOnline.
     *
     * @return ?int
     */
    public function getIsOnline(): ?int
    {
        return $this->isOnline;
    }

    /**
     * apply.
     *
     * @param  Builder  $builder
     * @param  Model  $model
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $isOnline = $this->getIsOnline();

        if ($isOnline) {
            $builder->where('is_online', $isOnline);
        }
    }
}
