<?php

namespace MetaFox\Activity\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Activity\Models\Type;
use MetaFox\Activity\Repositories\TypeRepositoryInterface;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;
use MetaFox\Platform\Support\Browse\Scopes\PackageScope;

/**
 * Class TypeScope.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class TypeScope extends BaseScope
{
    public function __construct(private TypeRepositoryInterface $typeRepository)
    {
    }

    /**
     * @var string
     */
    private ?string $tableAlias = null;

    /**
     * Get the value of tableAlias.
     *
     * @return ?string
     */
    public function getTableAlias(): ?string
    {
        return $this->tableAlias;
    }

    /**
     * Set the value of tableAlias.
     *
     * @param string $tableAlias
     *
     * @return self
     */
    public function setTableAlias(string $tableAlias)
    {
        $this->tableAlias = $tableAlias;

        return $this;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $table = $this->getTableAlias() ?? 'activity_feeds';
        $builder->whereIn("$table.type_id", $this->typeRepository->getActiveTypeValues());
    }

    /**
     * @param QueryBuilder $builder
     *
     * @return void
     */
    public function applyQueryBuilder(QueryBuilder $builder): void
    {
        $table = $this->getTableAlias() ?? 'activity_feeds';
        $builder->whereIn("$table.type_id", $this->typeRepository->getActiveTypeValues());
    }
}
