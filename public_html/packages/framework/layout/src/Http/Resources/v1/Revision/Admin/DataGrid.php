<?php

namespace MetaFox\Layout\Http\Resources\v1\Revision\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName = 'layout';

    protected string $resourceName = 'revision';

    protected function initialize(): void
    {
        $this->setDataSource(
            apiUrl('admin.layout.revision.index'),
            ['snippet' => ':snippet'],
            ['snippet' => ':snippet']
        );

        $this->addColumn('id')
            ->header('ID')
            ->width(80);

        $this->addColumn('name')
            ->header(__p('core::phrase.name'))
            ->flex(1);

        $this->addColumn('created_at')
            ->header(__p('core::phrase.updated_at'))
            ->asDateTime();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['destroy']);

            $actions->add('revert')
                ->apiUrl('/admincp/layout/revision/:id/revert')
                ->asPost();
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withDelete();

            $menu->addItem('revert')
                ->icon('ico-trash-o')
                ->value(Constants::ACTION_ROW_DELETE)
                ->label($label ?? __p('layout::phrase.revert_to_this_revision'))
                ->action('revert')
                ->confirm(['message' => __p('layout::web.revert_action_confirm_note')])
                ->showWhen(['truthy', 'item.can_revert']);
        });
    }
}
