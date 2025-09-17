<?php

namespace MetaFox\Friend\Support\Browse\Scopes\Friend;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;

/**
 * Class ViewFriendsScope.
 * @ignore
 * @codeCoverageIgnore
 */
class ViewFriendsScope extends BaseScope
{
    private int $userId;
    private int $listId        = 0;
    private string $searchText = '';
    private bool $isMention    = false;
    private ?string $table;
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
    public function getListId(): int
    {
        return $this->listId;
    }

    /**
     * @param int $listId
     *
     * @return ViewFriendsScope
     */
    public function setListId(int $listId): self
    {
        $this->listId = $listId;

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
     * @return bool
     */
    public function isMention(): bool
    {
        return $this->isMention;
    }

    /**
     * @param bool $isMention
     *
     * @return ViewFriendsScope
     */
    public function setIsMention(bool $isMention): self
    {
        $this->isMention = $isMention;

        return $this;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();

        $searchText = $this->getSearchText();

        $listId = $this->getListId();

        $userId = $this->getUserId();

        $builder->select('users.*')
            ->join('friends', function (JoinClause $join) use ($table, $userId) {
                $join->on("{$table}.id", '=', 'friends.owner_id');
                $join->where('friends.user_id', $userId);
            });

        if ($searchText != '') {
            $searchFields = $this->getSearchFields();
            if (!count($searchFields)) {
                $searchFields = ['user_name', 'full_name'];
            }

            $builder = $builder->addScope(new SearchScope($searchText, $searchFields));
        }

        if ($listId > 0) {
            $builder->join('friend_list_data AS fld', function (JoinClause $join) use ($listId) {
                $join->on('fld.user_id', '=', 'friends.owner_id');
                $join->where('fld.list_id', $listId);
            });
        }
        if ($this->isMention()) {
            // Who can tag me in written contexts?
            $builder->leftJoin('user_privacy_values as can_be_tagged', function (JoinClause $join) use ($table) {
                $join->on("{$table}.id", '=', 'can_be_tagged.user_id');
                $join->where('can_be_tagged.name', '=', 'user:can_i_be_tagged');
                $join->where('can_be_tagged.privacy', '=', MetaFoxPrivacy::ONLY_ME);
            });
            $builder->whereNull('can_be_tagged.id');
        }
    }

    public function applyQueryBuilder(QueryBuilder $builder): void
    {
        $table = $this->getTable();

        if (null === $table) {
            return;
        }

        $searchText = $this->getSearchText();

        $listId = $this->getListId();

        $userId = $this->getUserId();

        $isMention = $this->isMention();

        $builder->select('users.*')
            ->join('friends', function (JoinClause $join) use ($table, $userId) {
                $join->on("{$table}.id", '=', 'friends.owner_id');
                $join->where('friends.user_id', $userId);
            });

        if ($searchText != '') {
            $builder = $builder->addScope(new SearchScope($searchText, ['user_name', 'full_name']));
        }

        if ($listId > 0) {
            $builder->join('friend_list_data AS fld', function (JoinClause $join) use ($listId) {
                $join->on('fld.user_id', '=', 'friends.owner_id');
                $join->where('fld.list_id', $listId);
            });
        }

        if ($isMention) {
            // Who can tag me in written contexts?
            $builder->leftJoin('user_privacy_values as can_be_tagged', function (JoinClause $join) use ($table) {
                $join->on("{$table}.id", '=', 'can_be_tagged.user_id');
                $join->where('can_be_tagged.name', '=', 'user:can_i_be_tagged');
                $join->where('can_be_tagged.privacy', '=', MetaFoxPrivacy::ONLY_ME);
            });
            $builder->whereNull('can_be_tagged.id');
        }

        $builder->orderByDesc('friends.id');
    }
}
