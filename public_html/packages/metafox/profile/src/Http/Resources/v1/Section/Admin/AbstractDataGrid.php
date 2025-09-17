<?php

namespace MetaFox\Profile\Http\Resources\v1\Section\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class AbstractDataGrid.
 *
 * @codeCoverageIgnore
 * @ignore
 */
abstract class AbstractDataGrid extends Grid
{
    protected string $appName      = 'profile';
    protected string $resourceName = 'section';

    protected function initialize(): void
    {
        $this->setDataSource("admincp/profile/section?section_type={$this->getSectionType()}", $this->getApiParams());

        $this->getFormSearch();

        if ($this->enableOrder()) {
            $this->sortable();
        }

        $this->addColumn('name')
            ->header(__p('profile::phrase.group'))
            ->flex();

        $this->addColumn('label')
            ->header(__p('core::phrase.label'))
            ->flex();

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive();

        $this->addColumn('is_system')
            ->header(__p('core::phrase.is_system'))
            ->asYesNoIcon();
        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $this->getWithActions($actions);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $this->getWithItemMenu($menu);
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $this->getWithGidMenu($menu);
        });
    }

    protected function enableOrder(): bool
    {
        return true;
    }

    protected function getApiParams(): array
    {
        return [
            'profile_type' => $this->getSectionType(),
        ];
    }

    abstract public function getSectionType(): string;

    abstract protected function getAddItemMenu(GridActionMenu $menu): void;

    abstract protected function getAddItemAction(Actions $actions): void;

    public function boot(?int $parentId = null): void
    {
        $this->withActions(function (Actions $actions) {
            $this->getAddItemAction($actions);
        });
    }

    protected function getFormSearch(): void
    {
        $searchFrom = new SearchSectionForm();
        $this->setSearchForm($searchFrom);
    }

    protected function getWithGidMenu(GridActionMenu $menu): void
    {
        $this->getAddItemMenu($menu);
    }

    protected function getWithItemMenu(ItemActionMenu $menu): void
    {
        $menu->withEdit();
        $this->deleteItemMenu($menu);
    }

    protected function getWithActions(Actions $actions): void
    {
        $actions->addActions(['delete', 'destroy', 'toggleActive']);

        $actions->add('edit')
            ->asFormDialog(false)
            ->link('links.editItem');

        if ($this->enableOrder()) {
            $actions->add('orderItem')
                ->asPost()
                ->apiUrl('admincp/profile/section/order');
        }
    }

    protected function deleteItemMenu(ItemActionMenu $menu): void
    {
        $menu->withDeleteForm()->showWhen(['falsy', 'item.is_system']);
    }
}
