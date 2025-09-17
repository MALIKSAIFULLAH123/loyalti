<?php

namespace MetaFox\Photo\Support\Browse\Scopes\Photo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Scopes\PrivacyScope as BasePrivacyScope;

/**
 * Class PrivacyScope.
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class PrivacyScope extends BasePrivacyScope
{
    private bool $skipQueryOwner = false;

    /**
     * @return bool
     */
    public function getSkipQueryOwner(): bool
    {
        return $this->skipQueryOwner;
    }

    /**
     * @param  bool  $skipQueryOwner
     * @return $this
     */
    public function setSkipQueryOwner(bool $skipQueryOwner): self
    {
        $this->skipQueryOwner = $skipQueryOwner;

        return $this;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $this->addPrivacyScope($builder, $model);

        $this->addBlockedScope($builder, $model);

        if ($this->getSkipQueryOwner()) {
            return;
        }

        $ownerId = $this->getOwnerId();

        $resourceOwnerColumn = $model->getTable() . '.owner_id';

        if (null !== $ownerId) {
            $builder->where($resourceOwnerColumn, $ownerId);
        }
    }
}
