<?php

namespace MetaFox\Localize\Http\Resources\v1\Country\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Form\Html\BuiltinAdminSearchForm;
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
class DataGrid extends Grid
{
    protected string $appName = 'localize';

    protected string $resourceName = 'country';

    protected function initialize(): void
    {
        $this->setSearchForm(new BuiltinAdminSearchForm());

        $this->setDefaultDataSource();

        $this->setRowsPerPage(20, [20, 50, 100, 200, 500]);

        $this->addColumn('name')
            ->header(__p('core::phrase.name'))
            ->linkTo('url')
            ->truncateLines()
            ->flex();

        $this->addColumn('country_iso')
            ->header(__p('core::phrase.country_iso'))
            ->width(200);

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'delete', 'destroy', 'toggleActive']);

            $actions->add('addState')
                ->apiUrl(apiUrl('admin.localize.country.child.create'))
                ->apiParams([
                    'country_id' => ':id',
                ]);
        });

        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('addState')
                ->value(MetaFoxForm::ACTION_ROW_EDIT)
                ->label(__p('localize::phrase.add_new_state'))
                ->params([
                    'action' => 'addState',
                ]);
            $menu->addItem('manageMembers')
                ->value(MetaFoxForm::ACTION_ROW_LINK)
                ->label(__p('user::admin.manage_members'))
                ->params([
                    'to'     => '/admincp/user/user/browse?country=:country_iso&view_more=1',
                    'target' => '_blank',
                ]);

            $menu->withEdit()->reload();
            $menu->withDelete();
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('localize::country.add_country'))
                ->removeAttribute('value')
                ->to('localize/country/create');
        });
    }
}
