<?php

namespace MetaFox\Platform\Support\Browse\Scopes;

use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use MetaFox\Core\Repositories\DriverRepositoryInterface;

/**
 * Class EntityTypeScope.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class EntityTypeScope extends BaseScope
{
    /** @var string */
    private string $table;

    /** @var array<string, string> */
    private array $entities;

    /** @var string */
    private string $entityTypeColumn = 'item_type';

    /**
     * __construct.
     *
     * @param string        $table
     * @param array<string> $packages
     */
    public function __construct(string $table, array $entities = [])
    {
        $this->setTable($table);
        $this->setEntities($entities);
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $table
     *
     * @return self
     */
    public function setTable(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Get the value of entities.
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * Set the value of entities.
     *
     * @return self
     */
    public function setEntities($entities)
    {
        if (empty($entities)) {
            $entities = array_keys(resolve(DriverRepositoryInterface::class)->loadEntities());
        }

        $this->entities = $entities;

        return $this;
    }

    /**
     * Get the value of entityColumn.
     */
    public function getEntityTypeColumn()
    {
        return $this->entityTypeColumn;
    }

    /**
     * Set the value of entityColumn.
     *
     * @return self
     */
    public function setEntityTypeColumn($entityColumn)
    {
        $this->entityTypeColumn = $entityColumn;

        return $this;
    }

    private function hasColumn()
    {
        $column = $this->entityTypeColumn;
        $table = $this->getTable();

        if (!$table || !$column) {
            return false;
        }

        return Cache::rememberForever("$table.$column", function () use ($table, $column) {
            return Schema::hasColumn($table, $column);
        });
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();

        if (!$table || !$this->hasColumn()) {
            return;
        }

        // TODO: optimize whereIn / indexing in $table
        $builder->whereIn("$table.$this->entityTypeColumn", $this->getEntities());
    }

    public function applyQueryBuilder(QueryBuilder $builder): void
    {
        $table = $this->getTable();

        if (!$table || !$this->hasColumn()) {
            return;
        }

        // TODO: optimize whereIn / indexing in $table
        $builder->whereIn("$table.$this->entityTypeColumn", $this->getEntities());
    }
}
