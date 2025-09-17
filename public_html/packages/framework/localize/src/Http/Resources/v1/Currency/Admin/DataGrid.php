<?php

namespace MetaFox\Localize\Http\Resources\v1\Currency\Admin;

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

    protected string $resourceName = 'currency';

    protected function initialize(): void
    {
        $this->setSearchForm(new BuiltinAdminSearchForm());

        $this->addColumn('name')
            ->header(__p('localize::currency.name'))
            ->truncateLines()
            ->flex();

        $this->addColumn('symbol')
            ->alignCenter()
            ->header(__p('localize::currency.symbol'))
            ->minWidth(150)
            ->flex();

        $this->addColumn('format')
            ->header(__p('localize::currency.format'))
            ->alignCenter()
            ->flex();

        $this->addColumn('is_default')
            ->header(__p('core::web.default_ucfirst'))
            ->asToggleDefault()
            ->flex();

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->flex()
            ->asToggleActive();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'delete', 'destroy', 'toggleActive', 'toggleDefault']);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit()->reload();
            $menu->addItem('manageMembers')
                ->value(MetaFoxForm::ACTION_ROW_LINK)
                ->label(__p('user::admin.manage_members'))
                ->params([
                    'to'     => '/admincp/user/user/browse?currency_id=:code&view_more=1',
                    'target' => '_blank',
                ]);
            $menu->withDelete()
                ->showWhen([
                    'neqeqeq',
                    'item.is_active',
                    null,
                ]);
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('localize::currency.add_new_currency'))
                ->removeAttribute('value')
                ->to('localize/currency/create');
        });
    }
}
