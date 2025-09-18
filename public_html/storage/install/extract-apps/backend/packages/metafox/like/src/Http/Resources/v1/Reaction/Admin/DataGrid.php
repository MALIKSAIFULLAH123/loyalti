<?php

namespace MetaFox\Like\Http\Resources\v1\Reaction\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
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
    protected string $appName      = 'like';
    protected string $resourceName = 'reaction';

    protected function initialize(): void
    {
        $this->sortable();
        $this->dynamicRowHeight();
        $this->setSearchForm(new SearchReactionForm());
        $this->setDataSource(apiUrl('admin.like.reaction.index'), [
            'q'         => ':q',
            'is_active' => ':is_active',
        ]);

        $this->addColumn('title')
            ->header(__p('core::phrase.name'))
            ->width(200);

        $this->addColumn('preview')
            ->header(__p('like::phrase.image'))
            ->asPreviewUrl()
            ->alignCenter()
            ->width(100);

        $this->addColumn('icon_font')
            ->header(__p('app::phrase.icon'))
            ->alignCenter()
            ->asIcon();

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive()
            ->flex();

        $this->addColumn('is_default')
            ->header(__p('core::phrase.default'))
            ->asYesNoIcon()
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'delete', 'destroy', 'toggleActive']);
            $actions->addEditPageUrl();
            $actions->add('orderItem')
                ->asPost()
                ->apiUrl(apiUrl('admin.like.reaction.order'));

            $actions->add('default')
                ->asPost()
                ->apiUrl(apiUrl('admin.like.reaction.default', ['id' => ':id']));
        });

        /*
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();

            $menu->addItem('default')
                ->value(MetaFoxForm::ACTION_ROW_ACTIVE)
                ->params(['action' => 'default'])
                ->reload()
                ->showWhen([
                    'and',
                    ['falsy', 'item.is_default'],
                    ['neq', 'item.is_active', 0],
                ])
                ->label(__p('core::phrase.default'));

            $menu->withDeleteForm()
                ->showWhen([
                    'and',
                    ['falsy', 'item.is_default'],
                ]);
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->addItem('addItem')
                ->icon('ico-plus')
                ->label(__p('like::phrase.create_reaction'))
                ->disabled(false)
                ->to('like/reaction/create')
                ->params(['action' => 'addItem']);
        });
    }

    public function boot(?int $parentId = null): void
    {
        $this->withActions(function (Actions $actions) {
            $actions->add('addItem')
                ->apiUrl(apiUrl('admin.like.reaction.create'));
        });
    }
}
