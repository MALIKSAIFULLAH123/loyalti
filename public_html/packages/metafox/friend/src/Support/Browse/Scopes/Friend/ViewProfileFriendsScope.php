<?php

namespace MetaFox\Friend\Support\Browse\Scopes\Friend;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;

/**
 * Class ViewFriendsScope.
 * @ignore
 * @codeCoverageIgnore
 */
class ViewProfileFriendsScope extends BaseScope
{
    /**
     * @var int
     */
    protected int $userId = 0;

    /**
     * @var int
     */
    protected int $ownerId = 0;

    /**
     * @var string|null
     */
    protected ?string $table = null;

    /**
     * @var string
     */
    protected string $searchText = '';

    /**
     * @var array
     */
    protected array $searchFields = [];

    /**
     * @param  string $table
     * @return $this
     */
    public function setTable(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTable(): ?string
    {
        return $this->table;
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
     * @return ViewFriendsScope
     */
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOwnerId(): int
    {
        return $this->ownerId;
    }

    /**
     * @param int $userId
     *
     * @return ViewFriendsScope
     */
    public function setOwnerId(int $userId): self
    {
        $this->ownerId = $userId;

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
     * @return ViewFriendsScope
     */
    public function setSearchText(string $searchText): self
    {
        $this->searchText = $searchText;

        return $this;
    }

    /**
     * @param  array $fields
     * @return $this
     */
    public function setSearchFields(array $fields): self
    {
        $this->searchFields = $fields;

        return $this;
    }

    /**
     * @return array
     */
    public function getSearchFields(): array
    {
        return $this->searchFields;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $table = $this->getTable();

        if (null === $table) {
            $table = $model->getTable();
        }

        $searchText = $this->getSearchText();

        $ownerId = $this->getOwnerId();

        $userId = $this->getUserId();

        $mutualFriendBuilder = DB::table('friends', 'mf1')
            ->select(DB::raw('COUNT(*)'))
            ->join('friends as mf2', function (JoinClause $join) use ($userId) {
                $join->on('mf2.owner_id', '=', 'mf1.owner_id')
                    ->where('mf2.user_id', '=', $userId);
            })
            ->whereColumn("{$table}.id", '=', 'mf1.user_id');

        $builder->select([$table . '.*', DB::raw('(CASE WHEN f2.id IS NULL THEN 0 ELSE 1 END) as is_user_friend')])
            ->selectSub($mutualFriendBuilder, 'total_mutual_friend')
            ->join('friends as f1', function (JoinClause $join) use ($table, $ownerId) {
                $join->on("{$table}.id", '=', 'f1.owner_id');
                $join->where('f1.user_id', $ownerId);
            })
            ->leftJoin('friends as f2', function (JoinClause $join) use ($table, $userId) {
                $join->on('f2.owner_id', '=', "{$table}.id")
                    ->where('f2.user_id', '=', $userId);
            });

        if ($searchText != '') {
            $searchFields = $this->getSearchFields();

            if (!count($searchFields)) {
                $searchFields = ['user_name', 'full_name'];
            }

            $builder = $builder->addScope(new SearchScope($searchText, $searchFields));
        }

        $builder
            ->where("{$table}.id", '<>', $userId)
            ->orderByDesc('is_user_friend')
            ->orderByDesc('total_mutual_friend');
    }

    public function applyQueryBuilder(QueryBuilder $builder): void
    {
        $table = $this->getTable();

        if (null === $table) {
            return;
        }

        $searchText = $this->getSearchText();

        $ownerId = $this->getOwnerId();

        $userId = $this->getUserId();

        $mutualFriendBuilder = DB::table('friends', 'mf1')
            ->select(DB::raw('COUNT(*)'))
            ->join('friends as mf2', function (JoinClause $join) use ($userId) {
                $join->on('mf2.owner_id', '=', 'mf1.owner_id')
                    ->where('mf2.user_id', '=', $userId);
            })
            ->whereColumn("{$table}.id", '=', 'mf1.user_id');

        $builder->select([$table . '.*', 'f2.id AS is_user_friend'])
            ->selectSub($mutualFriendBuilder, 'total_mutual_friend')
            ->join('friends as f1', function (JoinClause $join) use ($table, $ownerId) {
                $join->on("{$table}.id", '=', 'f1.owner_id');
                $join->where('f1.user_id', $ownerId);
            })
            ->leftJoin('friends as f2', function (JoinClause $join) use ($table, $userId) {
                $join->on('f2.owner_id', '=', "{$table}.id")
                    ->where('f2.user_id', '=', $userId);
            });

        if ($searchText != '') {
            $searchFields = $this->getSearchFields();

            if (!count($searchFields)) {
                $searchFields = ['user_name', 'full_name'];
            }

            $builder = $builder->addScope(new SearchScope($searchText, $searchFields));
        }

        $builder->where("{$table}.id", '<>', $userId)
            ->orderBy('is_user_friend')
            ->orderByDesc('total_mutual_friend');
    }
}
