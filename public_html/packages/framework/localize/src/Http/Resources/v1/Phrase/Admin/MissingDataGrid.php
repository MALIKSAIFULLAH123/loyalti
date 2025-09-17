<?php

namespace MetaFox\Localize\Http\Resources\v1\Phrase\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class MissingDataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class MissingDataGrid extends Grid
{
    protected string $appName      = 'localize';
    protected string $resourceName = 'phrase';

    protected function initialize(): void
    {
        $this->setSearchForm(new SearchPhraseForm());
        $this->dynamicRowHeight();
        $this->setDataSource(apiUrl('admin.localize.phrase.missing'), [
            'q'          => ':q',
            'locale'     => ':locale',
            'group'      => ':group',
            'package_id' => ':package_id',
        ]);

        $this->addColumn('key')
            ->header(__p('localize::phrase.key_name'))
            ->truncateLines()
            ->flex(1);

        $this->addColumn('language')
            ->header(__p('localize::phrase.language'))
            ->width(200);

        $this->addColumn('group')
            ->header(__p('localize::phrase.group'))
            ->width(200);

        $this->addColumn('app_name')
            ->header(__p('core::phrase.package_name'))
            ->truncateLines()
            ->width(200);

        $this->addColumn('text')
            ->header(__p('localize::phrase.translation'))
            ->flex(1);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'delete', 'destroy']);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
            $menu->withDelete();
        });
    }
}
