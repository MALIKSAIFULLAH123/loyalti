<?php

namespace MetaFox\Platform\Http\Resources\v1\Category\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Form\Html\BuiltinAdminSearchForm;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\Contracts\CategoryRepositoryInterface;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
abstract class DataGrid extends Grid
{
    /**
     * @return CategoryRepositoryInterface
     */
    abstract protected function categoryRepository(): CategoryRepositoryInterface;

    protected const URL = 'admin.%s.%s.%s';

    protected string $appName      = 'core';
    protected string $resourceName = 'category';
    protected int    $level        = 1;

    public function boot(?int $parentId): void
    {
        if ($parentId) {
            $category    = $this->categoryRepository()->find($parentId);
            $this->level = $category->level;
        }

        $this->withActions(function (Actions $actions) {
            $this->getAddItemAction($actions);
        });
    }

    protected function initialize(): void
    {
        $this->sortable();
        $this->setSearchForm(new BuiltinAdminSearchForm());
        $this->dynamicRowHeight();

        $this->getNameColumn();
        $this->getParentColumn();
        $this->getIsActiveColumn();
        $this->getIsDefaultColumn();

        if ($this->level + 1 < MetaFoxConstant::MAX_CATEGORY_LEVEL) {
            $this->getToTalSubColumn();
        }

        $this->getToTalItemColumn();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['delete', 'destroy', 'toggleActive']);

            $this->editItemsAction($actions);
            $this->defaultAction($actions);
            $this->orderItemAction($actions);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $this->defaultMenu($menu);
            $this->editItemMenu($menu);
            $this->deleteItemMenu($menu);
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $this->getAddItemMenu($menu);
        });
    }

    protected function getNameColumn(): void
    {
        $this->addColumn('name')
            ->header(__p('core::phrase.name'))
            ->truncateLines()
            ->flex();
    }

    protected function getParentColumn(): void
    {
        $this->addColumn('parent.name')
            ->header(__p('core::phrase.parent_category'))
            ->truncateLines()
            ->flex();
    }

    protected function getIsActiveColumn(): void
    {
        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive()
            ->width(120);
    }

    protected function getIsDefaultColumn(): void
    {
        $this->addColumn('is_default')
            ->header(__p('core::phrase.default'))
            ->asYesNoIcon()
            ->reload(true)
            ->width(120);
    }

    protected function getToTalSubColumn(): void
    {
        $this->addColumn('total_sub')
            ->header(__p('core::phrase.sub_categories'))
            ->asNumber()
            ->width(150)
            ->alignCenter()
            ->linkTo('total_sub_link');
    }

    protected function getToTalItemColumn(): void
    {

    }

    protected function editItemsAction(Actions $actions): void
    {
        $actions->add('editItem')
            ->apiUrl(sprintf('admincp/core/form/%s.%s_%s.%s', $this->appName, $this->appName, $this->resourceName, 'update') . '/:id');
    }

    protected function defaultAction(Actions $actions): void
    {
        $actions->add('default')
            ->asPost()
            ->apiUrl(apiUrl($this->getUrl('default'), ['id' => ':id']));
    }

    protected function orderItemAction(Actions $actions): void
    {
        $actions->add('orderItem')
            ->asPost()
            ->apiUrl(apiUrl($this->getUrl('order')));
    }

    protected function editItemMenu(ItemActionMenu $menu): void
    {
        $menu->withEdit()
            ->params(['action' => 'editItem']);
    }

    protected function defaultMenu(ItemActionMenu $menu): void
    {
        $menu->addItem('default')
            ->value(MetaFoxForm::ACTION_ROW_ACTIVE)
            ->params(['action' => 'default'])
            ->reload(true)
            ->showWhen([
                'and',
                ['falsy', 'item.is_default'],
                ['neq', 'item.is_active', 0],
            ])
            ->label(__p('core::phrase.mark_as_default'));
    }

    protected function deleteItemMenu(ItemActionMenu $menu): void
    {
        $menu->withDeleteForm()
            ->showWhen([
                'and',
                ['neq', 'item.is_active', null],
                ['falsy', 'item.is_default'],
            ]);
    }

    protected function getAddItemAction(Actions $actions): void
    {
        $actions->add('addItem')
            ->apiUrl(apiUrl($this->getUrl('create')));
    }

    protected function getAddItemMenu(GridActionMenu $menu): void
    {
        $menu->addItem('addItem')
            ->icon('ico-plus')
            ->label(__p('core::phrase.add_category'))
            ->disabled(false)
            ->to(sprintf('%s/%s/%s', $this->appName, $this->resourceName, 'create'))
            ->params(['action' => 'addItem']);
    }

    protected function getUrl(string $action): string
    {
        return sprintf(self::URL, $this->appName, $this->resourceName, $action);
    }
}
