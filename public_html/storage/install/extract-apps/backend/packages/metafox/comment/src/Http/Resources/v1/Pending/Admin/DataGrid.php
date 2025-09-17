<?php

namespace MetaFox\Comment\Http\Resources\v1\Pending\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
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
    protected string $appName      = 'comment';
    protected string $resourceName = 'pending';

    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        $this->addColumn('date')
            ->header(__p('core::phrase.date'))
            ->asDateTime()
            ->flex();

        $this->addColumn('user_name')
            ->header(__p('user::phrase.user'))
            ->linkTo('user_link')
            ->target('_blank')
            ->truncateLines()
            ->flex();
        $this->addColumn('item_name')
            ->header(__p('comment::phrase.item_name'))
            ->linkTo('link')
            ->target('_blank')
            ->truncateLines()
            ->flex();

        $this->addColumn('item_type')
            ->header(__p('comment::phrase.item_type'))
            ->truncateLines()
            ->flex();

        $this->addColumn('text')
            ->header(__p('comment::phrase.content'))
            ->truncateLines()
            ->asHtml()
            ->flex()
            ->setAttribute('sx', [
                'word-break' => 'break-all',
            ]);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['delete', 'destroy']);

            $actions->add('approve')
                ->apiUrl('admincp/comment/pending/approve/:id')
                ->asPatch();
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('approve')
                ->action('approve')
                ->label(__p('core::phrase.approve'))
                ->showWhen(['truthy', 'item.extra.can_approve'])
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->reload();

            $menu->withDelete()
                ->label(__p('comment::phrase.decline'));
        });
    }
}
