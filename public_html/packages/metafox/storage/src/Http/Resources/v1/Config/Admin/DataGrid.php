<?php

namespace MetaFox\Storage\Http\Resources\v1\Config\Admin;

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
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'storage';
    protected string $resourceName = 'option';

    protected function initialize(): void
    {
        // $this->enableCheckboxSelection();
        $this->inlineSearch(['driver']);
        $this->setSearchForm(new BuiltinAdminSearchForm());

        $this->addColumn('id')
            ->asId()
            ->width(200);

        $this->addColumn('driver')
            ->header(__p('storage::phrase.driver'))
            ->flex(1);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['destroy']);

            $actions->add('deleteConfig')
                ->asGet()
                ->apiUrl('admincp/core/form/storage.config.delete?name=:id');

            $actions->add('edit')
                ->asFormDialog(false)
                ->link('links.edit');
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit()
                ->showWhen(['truthy', 'item.can_edit']);

            $menu->addItem('delete')
                ->action('deleteConfig')
                ->reload()
                ->value(MetaFoxForm::ACTION_ROW_EDIT)
                ->showWhen(['truthy', 'item.can_delete'])
                ->label(__p('core::phrase.delete'));
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('storage::phrase.add_new_config'))
                ->removeAttribute('value')
                ->to('storage/option/create');
        });
    }
}
