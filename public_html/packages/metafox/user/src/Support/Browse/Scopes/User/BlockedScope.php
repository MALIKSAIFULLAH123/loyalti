<?php

namespace MetaFox\User\Support\Browse\Scopes\User;

use Illuminate\Contracts\Database\Query\Builder as BuilderContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class BlockedScope extends BaseScope
{
    private int $contextId;

    private ?string $table = null;

    private ?string $primaryKey = null;
    private ?string $secondKey  = null;

    public function getSecondKey(): ?string
    {
        return $this->secondKey;
    }

    public function setSecondKey(?string $secondKey): void
    {
        $this->secondKey = $secondKey;
    }

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
     * Get the value of table.
     */
    public function getTable(): ?string
    {
        if (null === $this->table) {
            return 'users';
        }

        return $this->table;
    }

    /**
     * Set the value of table.
     *
     * @return self
     */
    public function setTable(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Get the value of primary key.
     */
    public function getPrimaryKey(): ?string
    {
        if (null === $this->primaryKey) {
            return 'id';
        }

        return $this->primaryKey;
    }

    /**
     * Set the value of primary key.
     *
     * @return self
     */
    public function setPrimaryKey(string $primaryKey): self
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model): void
    {
        $this->buildQuery($builder, 'owner_id', 'user_id');
        $this->buildQuery($builder, 'user_id', 'owner_id');
    }

    public function applyQueryBuilder(QueryBuilder $builder): void
    {
        $this->buildQuery($builder, 'owner_id', 'user_id');
        $this->buildQuery($builder, 'user_id', 'owner_id');
    }

    protected function buildQuery(BuilderContract $builder, string $select, string $where): void
    {
        $contextId = $this->getContextId();

        if (!$contextId) {
            return;
        }

        $limit      = 50;
        $table      = $this->getTable();
        $primaryKey = $this->getPrimaryKey();
        $secondKey  = $this->getSecondKey();

        $userIds = \MetaFox\User\Models\UserBlocked::query()
            ->select($select)
            ->where($where, "=", $this->getContextId())
            ->limit($limit)
            ->pluck($select)
            ->toArray();

        if (empty($userIds)) {
            return;
        }

        if (count($userIds) < $limit) {
            $builder->whereNotIn("$table.$primaryKey", $userIds);
            if ($secondKey) {
                $builder->whereNotIn("$table.$secondKey", $userIds);
            }

            return;
        }

        // Resources post by users blocked you.
        $builder->whereNotIn("$table.$primaryKey", function ($query) use ($select, $where) {
            $query->select($select)->from('user_blocked')->where($where, '=', $this->getContextId());
        });

        if ($secondKey) {
            $builder->whereNotIn("$table.$secondKey", function ($query) use ($select, $where) {
                $query->select($select)->from('user_blocked')->where($where, '=', $this->getContextId());
            });
        }
    }
}
