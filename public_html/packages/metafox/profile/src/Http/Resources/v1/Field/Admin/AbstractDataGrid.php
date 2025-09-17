<?php

namespace MetaFox\Profile\Http\Resources\v1\Field\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 *
 * @codeCoverageIgnore
 * @ignore
 */
abstract class AbstractDataGrid extends Grid
{
    protected string $appName      = 'profile';
    protected string $resourceName = 'field';

    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        if ($this->enableOrder()) {
            $this->sortable();
        }

        $this->setRowsPerPage(20, [20, 50, 100, 200, 500]);

        $this->setDataSource("admincp/profile/field?section_type={$this->getSectionType()}", $this->getApiParams(), $this->getApiRules());

        $this->getFormSearch();

        $this->addColumn('field_name')
            ->header(__p('core::phrase.name'))
            ->flex();

        $this->addColumn('label')
            ->header(__p('core::phrase.label'))
            ->flex();

        $this->addColumn('group')
            ->header(__p('profile::phrase.group'))
            ->flex();

        $this->getRolesColumn();

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->width(250)
            ->asToggleActive();

        $this->getIsRegisterColumn();
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

    abstract public function getSectionType(): string;

    abstract protected function getAddItemMenu(GridActionMenu $menu): void;

    abstract protected function getAddItemAction(Actions $actions): void;

    abstract protected function getRolesColumn(): void;

    abstract protected function getIsRegisterColumn(): void;

    public function boot(?int $parentId = null): void
    {
        $this->withActions(function (Actions $actions) {
            $this->getAddItemAction($actions);
        });
    }

    protected function enableOrder(): bool
    {
        return true;
    }

    protected function getApiRules(): array
    {
        return [
            'section_type' => ['truthy', 'section_type'],
        ];
    }

    protected function getApiParams(): array
    {
        return [
            'section_type' => $this->getSectionType(),
        ];
    }

    protected function getFormSearch(): void
    {
        $searchFrom = new SearchFieldForm();
        $searchFrom->setSectionType($this->getSectionType());

        $this->setSearchForm($searchFrom);
    }

    protected function getWithGidMenu(GridActionMenu $menu): void
    {
        $this->getAddItemMenu($menu);
    }

    protected function getWithItemMenu(ItemActionMenu $menu): void
    {
        $menu->withEdit();

        $menu->addItem('duplicate')
            ->icon('ico-plus')
            ->label(__p('profile::phrase.duplicate'))
            ->value(MetaFoxForm::ACTION_ROW_EDIT)
            ->to("{$this->getSectionType()}/field/duplicate/:id")
            ->params(['action' => 'duplicate']);

        $menu->withDelete();
    }

    protected function getWithActions(Actions $actions): void
    {
        $actions->addActions(['destroy', 'toggleActive']);
        $actions->add('edit')
            ->asFormDialog(false)
            ->link('links.editItem');

        $this->getActionDuplicate($actions);

        if ($this->enableOrder()) {
            $actions->add('orderItem')
                ->asPost()
                ->apiUrl('admincp/profile/field/order');
        }

        $actions->add('register')
            ->apiUrl('admincp/profile/field/register/:id');
    }

    protected function getActionDuplicate(Actions $actions): void
    {
        $actions->add('duplicate')
            ->asFormDialog(false)
            ->asGet()
            ->pageUrl("/profile/field/duplicate/:id")
            ->apiUrl(apiUrl('admin.profile.field.duplicate', ['id' => ':id']));

    }
}
