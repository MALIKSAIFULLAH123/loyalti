<?php

namespace MetaFox\Page\Support\Browse\Scopes\PageMember;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ExtraTagScope.
 */
class ExtraTagScope extends BaseScope
{
    /**
     * @var int
     */
    private int $pageId;

    /**
     * @return int
     */
    public function getPageId(): int
    {
        return $this->pageId;
    }

    /**
     * @param int $pageId
     *
     * @return ExtraTagScope
     */
    public function setPageId(int $pageId): self
    {
        $this->pageId = $pageId;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $pageId = $this->getPageId();

        $builder->join('page_members AS pm', function (JoinClause $joinClause) use ($pageId) {
            $joinClause->on('pm.user_id', '=', 'users.id')
                ->where('pm.page_id', '=', $pageId);
        });
    }
}
