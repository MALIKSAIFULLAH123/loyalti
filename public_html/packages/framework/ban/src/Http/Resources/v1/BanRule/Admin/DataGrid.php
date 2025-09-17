<?php

namespace MetaFox\Ban\Http\Resources\v1\BanRule\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Html\BuiltinAdminSearchForm;
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
abstract class DataGrid extends Grid
{
    protected string $appName      = 'ban';
    protected string $resourceName = 'ban-rule';

    /**
     * @var array|array[]
     */
    protected array $apiRules = [
        'q'    => ['truthy', 'q'],
        'type' => ['truthy', 'type'],
    ];

    protected function initialize(): void
    {
        $this->setupInitialize();

        $this->addFindValueColumn();

        $this->addOtherColumns();

        $this->addColumn('user.display_name')
            ->header(__p('ban::phrase.added_by'))
            ->linkTo('user.url')
            ->target('_blank')
            ->truncateLines()
            ->flex();

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive()
            ->width(120);

        $this->addColumn('created_at')
            ->header(__p('ban::phrase.added_on'))
            ->asDateTime()
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['destroy', 'toggleActive']);

            $actions->add('batchDelete')
                ->asDelete()
                ->asFormDialog(false)
                ->apiUrl('admincp/ban/ban-rule/batch-delete?id=[:id]')
                ->confirm(['message' => __p('core::phrase.are_you_sure')]);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withDelete()
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('ban::phrase.delete_confirm'),
                ]);
        });

        $this->withBatchMenu(function (BatchActionMenu $menu) {
            $menu->addItem('batchDelete')
                ->action('batchDelete')
                ->icon('ico-trash-o')
                ->label(__p('core::phrase.delete'))
                ->reload()
                ->asBatchEdit();
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $this->addCreateMenu($menu);
        });
    }

    protected function addFindValueColumn(): void {}

    protected function addCreateMenu(GridActionMenu $menu): void {}

    protected function addOtherColumns(): void {}

    protected function setupInitialize(): void
    {
        $this->setAttribute('allowRiskParams', false);

        $this->setSearchForm(new BuiltinAdminSearchForm());
        $this->setRowsPerPage(20, [10, 20, 50]);
        $this->enableCheckboxSelection();

        $this->setDataSource(apiUrl('admin.ban.ban-rule.index'), $this->apiParams, $this->apiRules);
    }
}
