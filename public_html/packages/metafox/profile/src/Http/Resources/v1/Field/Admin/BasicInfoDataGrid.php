<?php

namespace MetaFox\Profile\Http\Resources\v1\Field\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;
use MetaFox\Profile\Support\CustomField as CustomFieldSupport;

/**
 * Class BasicInfoDataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class BasicInfoDataGrid extends Grid
{
    protected string $appName      = 'profile';
    protected string $resourceName = 'field_basic_info';

    protected function initialize(): void
    {
        $this->dynamicRowHeight();
        $this->sortable();

        $this->setDataSource("admincp/profile/field_basic_info?section_type={$this->getSectionType()}", $this->getApiParams(), $this->getApiRules());

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

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->width(250)
            ->asToggleActive();

        $this->addColumn('is_required')
            ->header(__p('profile::phrase.required'))
            ->width(250)
            ->asToggle('require');

        $this->addColumn('is_register')
            ->header(__p('profile::phrase.is_register'))
            ->width(250)
            ->fieldDisabled('disable_register')
            ->asToggle('register');

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
    }

    protected function getApiRules(): array
    {
        return [
            'section_type' => ['truthy', 'section_type'],
        ];
    }

    protected function getFormSearch(): void
    {
        $searchFrom = new SearchFieldForm();
        $searchFrom->setEnableSearchRole(false);
        $searchFrom->setSectionType($this->getSectionType());

        $this->setSearchForm($searchFrom);
    }

    protected function getWithItemMenu(ItemActionMenu $menu): void {}

    protected function getWithActions(Actions $actions): void
    {
        $actions->add('orderItem')
            ->asPost()
            ->apiUrl('admincp/profile/field/order');

        $actions->add('toggleActive')
            ->apiUrl('admincp/profile/field/active/:id');

        $actions->add('require')
            ->apiUrl('admincp/profile/field_basic_info/require/:id');

        $actions->add('register')
            ->apiUrl('admincp/profile/field/register/:id');
    }

    protected function getApiParams(): array
    {
        return [
            'section_type' => $this->getSectionType(),
        ];
    }

    private function getSectionType(): string
    {
        return CustomFieldSupport::SECTION_TYPE_USER;
    }
}
