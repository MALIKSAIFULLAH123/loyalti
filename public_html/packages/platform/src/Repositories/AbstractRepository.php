<?php

namespace MetaFox\Platform\Repositories;

use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\InputCleaner;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\CategoryScope;
use MetaFox\Platform\Support\Browse\Scopes\EntityTypeScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\TagScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Traits\Helpers\InputCleanerTrait;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class AbstractRepository.
 */
abstract class AbstractRepository extends BaseRepository implements InputCleaner
{
    use InputCleanerTrait;

    protected $skipPresenter = true;

    protected $disableSponsor;

    /**
     * @param array $params
     *
     * @return $this
     * @throws RepositoryException
     */
    public function where(array $params): self
    {
        $this->applyConditions($params);

        return $this;
    }

    public function createMany(array $items): bool
    {
        foreach ($items as $item) {
            $this->create($item);
        }

        return true;
    }

    public function hasSponsorView(array $attributes): bool
    {
        $view        = Arr::get($attributes, 'view');
        $currentPage = Arr::get($attributes, 'current_page');
        $category    = Arr::get($attributes, 'category_id');

        if ($this->isNoSponsorView($view)) {
            return false;
        }

        if (1 < $currentPage) {
            return false;
        }

        if ($category) {
            return false;
        }

        return true;
    }

    public function isNoSponsorView(?string $view): bool
    {
        return in_array($view, [
            Browse::VIEW_MY,
            Browse::VIEW_SIMILAR,
        ]);
    }

    /**
     * return the case-insensitive LIKE operator bases on the current database driver.
     *
     * @return string
     */
    public function likeOperator(): string
    {
        return database_driver() == 'pgsql' ? 'ilike' : 'like';
    }

    public function buildQueryScopes($query, $model, $criteria)
    {
        /** @var \MetaFox\Platform\Support\Browse\Scopes\BaseScope[] $scopes */
        $scopes = Arr::map([
            WhenScope::class,
            SearchScope::class,
            TagScope::class,
            CategoryScope::class,
        ], function (string $abstract) use ($query, $model, &$criteria) {
            /** @var \MetaFox\Platform\Support\Browse\Scopes\BaseScope $scope */
            $scope = $this->app->make($abstract);

            $result = $scope->buildQueryScope($query, $model, $criteria);

            return $result !== false ? $scope : null;
        });

        $scopes = Arr::where(
            $scopes,
            fn ($scope) => (bool) $scope
        );

        foreach ($scopes as $scope) {
            $scope->apply($query, $model);
        }
    }

    /**
     * @return array<string>
     */
    public function getUserContractMorphTypeColumns(): array
    {
        return ['user_type', 'owner_type'];
    }

    public function withUserMorphTypeActiveScope(): self
    {
        if (!method_exists($this, 'getUserContractMorphTypeColumns')) {
            return $this;
        }

        $userMorphTypeColumns = $this->getUserContractMorphTypeColumns();

        if (empty($userMorphTypeColumns)) {
            return $this;
        }

        foreach ($userMorphTypeColumns as $column) {
            $scope = new EntityTypeScope($this->model->getTable());

            $this->model->addGlobalScope($scope->setEntityTypeColumn($column));
        }

        return $this;
    }
}
