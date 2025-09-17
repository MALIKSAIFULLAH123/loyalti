<?php

namespace MetaFox\Page\Http\Resources\v1\PageClaim\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Page\Support\PageClaimSupport;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'page';
    protected string $resourceName = 'claim';

    protected function initialize(): void
    {
        $this->setRowsPerPage(20, [20, 50, 100]);

        $this->setDataSource('/admincp/page/claim', ['q' => ':q']);
        $this->dynamicRowHeight();

        $this->addColumn('user.display_name')
            ->header(__p('page::phrase.claimed_by'))
            ->linkTo('user.url')
            ->width(150);

        $this->addColumn('page.user.display_name')
            ->header(__p('page::phrase.page_owner'))
            ->linkTo('page.user.url')
            ->width(200);

        $this->addColumn('page.title')
            ->header(__p('page::phrase.page'))
            ->linkTo('page.url')
            ->width(300);

        $this->addColumn('description')
            ->header(__p('core::phrase.description'))
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->add('grant')
                ->apiUrl('admincp/page/claim/:id')
                ->apiParams(['status' => PageClaimSupport::STATUS_APPROVE])
                ->asPatch();
            $actions->add('deny')
                ->apiUrl('admincp/page/claim/:id')
                ->apiParams(['status' => PageClaimSupport::STATUS_DENY])
                ->asPatch();
        });

        /*
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
            // $menu->asButton();
            // $menu->withDelete();
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('grant')
                ->action('grant')
                ->label(__p('page::phrase.grant'))
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->reload();

            $menu->addItem('deny')
                ->action('deny')
                ->label(__p('page::phrase.deny'))
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->reload();
        });

        $this->withExtraData([
            'show_total'        => true,
            'total_item_phrase' => 'claim_total_value_pages',
        ]);
    }
}
