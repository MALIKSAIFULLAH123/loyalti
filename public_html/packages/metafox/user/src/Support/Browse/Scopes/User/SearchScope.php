<?php

namespace MetaFox\User\Support\Browse\Scopes\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class SearchScope extends BaseScope
{
    /**
     * @var string|null
     */
    private ?string $aliasJoinedTable = null;
    private ?string $fieldJoined      = null;

    private string $searchText;

    /** @var string|null */
    private ?string $table = null;

    public function getTable(): ?string
    {
        return $this->table;
    }

    public function setTable(?string $table): void
    {
        $this->table = $table;
    }

    public function getSearchText(): string
    {
        return $this->searchText;
    }

    public function setSearchText(string $searchText): void
    {
        $this->searchText = $searchText;
    }

    public function getAliasJoinedTable(): ?string
    {
        return $this->aliasJoinedTable;
    }

    public function setAliasJoinedTable(?string $aliasJoinedTable): void
    {
        $this->aliasJoinedTable = $aliasJoinedTable;
    }

    public function getFieldJoined(): ?string
    {
        return $this->fieldJoined;
    }

    public function setFieldJoined(?string $fieldJoined): void
    {
        $this->fieldJoined = $fieldJoined;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model): void
    {
        $table            = $this->getTable() ?? $model->getTable();
        $searchUser       = $this->getSearchText();
        $aliasJoinedTable = $this->getAliasJoinedTable() ?? 'user';
        $fieldJoined      = $this->getFieldJoined() ?? 'user_id';

        $builder->leftJoin("user_entities as $aliasJoinedTable", "$aliasJoinedTable.id", '=', "$table.$fieldJoined");
        $builder->where(function (Builder $builder) use ($searchUser, $aliasJoinedTable) {
            $builder->where("$aliasJoinedTable.user_name", $this->likeOperator(), '%' . $searchUser . '%');
            $builder->orWhere("$aliasJoinedTable.name", $this->likeOperator(), '%' . $searchUser . '%');
        });
    }
}
