<?php

namespace MetaFox\Page\Support\Browse\Scopes\Page;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class BlockedScope extends BaseScope
{
    private int $contextId;

    /**
     * Get the value of contextId.
     */
    public function getContextId(): int
    {
        return $this->contextId;
    }

    /**
     * Set the value of contextId.
     *
     * @return self
     */
    public function setContextId(int $contextId)
    {
        $this->contextId = $contextId;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, Model $model)
    {
        $contextId = $this->getContextId();

        if ($contextId) {
            $builder->leftJoin('page_blocks', function (JoinClause $join) use ($contextId) {
                $join->on('pages.id', '=', 'page_blocks.page_id')
                    ->where('page_blocks.user_id', '=', $contextId);
            })->whereNull('page_blocks.id');
        }

    }
}

