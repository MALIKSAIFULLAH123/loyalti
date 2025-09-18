<?php

namespace MetaFox\Group\Support\Browse\Scopes\GroupMember;

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
    private int $groupId;

    /**
     * @return int
     */
    public function getGroupId(): int
    {
        return $this->groupId;
    }

    /**
     * @param int $groupId
     *
     * @return ExtraTagScope
     */
    public function setGroupId(int $groupId): self
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $groupId = $this->getGroupId();

        $builder->join('group_members AS gm', function (JoinClause $joinClause) use ($groupId) {
            $joinClause->on('gm.user_id', '=', 'users.id')
                ->where('gm.group_id', '=', $groupId);
        });
    }
}
