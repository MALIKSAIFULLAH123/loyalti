<?php

namespace MetaFox\Friend\Support\Browse\Scopes\Friend;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;

/**
 * Class ViewMutualFriendsScope.
 * @ignore
 * @codeCoverageIgnore
 */
class ViewMutualFriendsScope extends BaseScope
{
    private int $contextId;
    private int $userId;
    private string $searchText = '';

    /**
     * @return int
     */
    public function getContextId(): int
    {
        return $this->contextId;
    }

    /**
     * @param int $contextId
     *
     * @return ViewMutualFriendsScope
     */
    public function setContextId(int $contextId): self
    {
        $this->contextId = $contextId;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     *
     * @return ViewMutualFriendsScope
     */
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchText(): string
    {
        return $this->searchText;
    }

    /**
     * @param string $searchText
     *
     * @return ViewMutualFriendsScope
     */
    public function setSearchText(string $searchText): self
    {
        $this->searchText = $searchText;

        return $this;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $table      = $model->getTable();
        $searchText = $this->getSearchText();

        if ($searchText != '') {
            $builder = $builder->addScope(new SearchScope($searchText, ['user_name', 'full_name']));
        }

        $builder->whereIn("{$table}.id", function ($query) {
            $query->select('f.owner_id')
                ->from('friends as f')
                ->join('friends AS f2', function (JoinClause $join) {
                    $join->on('f.owner_id', '=', 'f2.owner_id')
                        ->where('f.user_id', $this->getContextId())
                        ->where('f2.user_id', $this->getUserId());
                });
        });
    }
}
