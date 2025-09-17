<?php
namespace MetaFox\Event\Support\Browse\Scopes\Member;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;
use Illuminate\Contracts\Database\Query\Builder as BuilderContract;

class SortScope extends BaseScope
{
    /**
     * @var string
     */
    private string $view = ViewScope::VIEW_ALL;

    /**
     * @param string $view
     * @return $this
     */
    public function setView(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $this->applyBuilderScope($builder, $model->getTable());
    }

    public function applyQueryBuilder(QueryBuilder $builder): void
    {
        $this->applyBuilderScope($builder, 'event_members');
    }

    private function applyBuilderScope(BuilderContract $builder, string $table)
    {
        $view = $this->view;

        switch ($view) {
            case ViewScope::VIEW_HOST:
                $builder->orderByDesc(sprintf('%s.created_at', $table));
                break;
        }
    }
}
