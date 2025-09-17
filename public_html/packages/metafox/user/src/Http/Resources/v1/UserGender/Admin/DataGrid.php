<?php

namespace MetaFox\User\Http\Resources\v1\UserGender\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
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
    protected string $appName      = 'user';
    protected string $resourceName = 'user-gender';

    protected function initialize(): void
    {
        $this->title(__p('user::phrase.manage_genders'));
        $this->setSearchForm(new SearchUserGenderForm());

        $this->addColumn('phrase')
            ->header(__p('core::phrase.phrase'))
            ->flex();

        $this->addColumn('name')
            ->header(__p('core::phrase.name'))
            ->flex();

        $this->addColumn('is_system')
            ->header(__p('core::phrase.is_system'))
            ->asYesNoIcon()
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'destroy']);
        });

        /*
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
            $menu->asButton();
            // $menu->withDelete();
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('user::phrase.add_new_gender'))
                ->removeAttribute('value')
                ->to('user/user-gender/create');
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
            $menu->withDelete(
                null,
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('user::phrase.delete_confirm_user_gender'),
                ],
                ['truthy', 'item.is_custom']
            );
        });
    }
}
