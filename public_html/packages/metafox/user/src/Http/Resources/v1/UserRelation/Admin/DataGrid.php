<?php

namespace MetaFox\User\Http\Resources\v1\UserRelation\Admin;

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
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'user';
    protected string $resourceName = 'relation';

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function initialize(): void
    {
        $this->setSearchForm(new SearchUserRelationForm());

        $this->setDataSource(apiUrl('admin.user.relation.index'), []);

        $this->addColumn('title')
            ->header(__p('core::phrase.title'))
            ->flex();

        $this->addColumn('phrase_var')
            ->header(__p('core::phrase.phrase'))
            ->flex();

        $this->addColumn('avatar')
            ->header(__p('app::phrase.icon'))
            ->setAttribute('variant', 'square')
            ->renderAs('AvatarCell')
            ->width(200);

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive()
            ->width(150);

        $this->addColumn('is_custom')
            ->header(__p('core::phrase.is_custom'))
            ->asYesNoIcon()
            ->width(150);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'destroy', 'toggleActive']);

            $actions->addEditPageUrl();
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('user::phrase.add_new_relation'))
                ->removeAttribute('value')
                ->to('user/relation/create');
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
            $menu->withDelete()
                ->showWhen(['and', ['truthy', 'item.is_custom']]);
        });
    }
}
