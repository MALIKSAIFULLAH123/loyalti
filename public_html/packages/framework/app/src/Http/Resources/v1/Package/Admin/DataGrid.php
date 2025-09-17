<?php

namespace MetaFox\App\Http\Resources\v1\Package\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\App\Support\Browse\Scopes\Package\SortScope;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 * @ignore
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class DataGrid extends Grid
{
    protected string $appName = 'app';

    protected string $resourceName = 'package';

    protected function initialize(): void
    {
        $this->setSearchForm(new SearchPackageForm());
        
        $this->enableCheckboxSelection();
        $this->setDataSource(apiUrl('admin.app.package.index'), [
            'q'         => ':q',
            'status'    => ':status',
            'sort'      => ':sort',
            'sort_type' => ':sort_type',
        ]);

        $this->addColumn('title')
            ->header(__p('core::phrase.title'))
            ->linkTo('internal_admin_url')
            ->sortable()
            ->sortableField(SortScope::SORT_TITLE_COLUMN)
            ->flex(2, 200);

        $this->addColumn('type')
            ->header(__p('core::phrase.type'))
            ->sortable()
            ->sortableField(SortScope::SORT_TYPE_COLUMN)
            ->flex(1, 120);

        $this->addColumn('version')
            ->header(__p('app::phrase.installed_version'))
            ->alignCenter()
            ->flex(1, 120);

        $this->addColumn('latest_version')
            ->header(__p('app::phrase.upgradable_version'))
            ->alignCenter()
            ->flex(1, 120);

        $this->addColumn('upgrade_available')
            ->header(__p('app::phrase.updates_available'))
            ->hint('upgrade_available_hint')
            ->linkTo('upgrade_available_link')
            ->sortable()
            ->sortableField(SortScope::SORT_UPDATE_AVAILABLE)
            ->alignCenter()
            ->flex(1, 120);

        $this->addColumn('author.name')
            ->header(__p('app::phrase.author'))
            ->linkTo('author.url')
            ->target('_blank')
            ->sortable()
            ->sortableField(SortScope::SORT_AUTHOR_COLUMN)
            ->alignCenter()
            ->flex(1, 120);

        $this->addColumn('is_core')
            ->header(__p('app::phrase.is_core'))
            ->asYesNoIcon()
            ->sortable()
            ->sortableField(SortScope::SORT_IS_CORE_COLUMN)
            ->flex(1, 120);

        $this->addColumn('is_active')
            ->header(__p('app::phrase.is_active'))
            ->asToggleActive()
            ->fieldDisabled('is_core')
            ->sortable()
            ->sortableField(SortScope::SORT_IS_ACTIVE_COLUMN)
            ->flex(1, 120);

        $this->addColumn('is_expired')
            ->header(__p('app::phrase.expired_at'))
            ->asYesNoIcon()
            ->flex(1, 120);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['export', 'edit', 'toggleActive', 'destroy']);

            $actions->add('active')
                ->asPatch()
                ->apiUrl(apiUrl('admin.app.package.toggleActive', ['package' => ':id']))
                ->confirm(['message' => __p('app::phrase.active_package_confirm_desc')]);

            $actions->add('inactive')
                ->asPatch()
                ->apiUrl(apiUrl('admin.app.package.toggleActive', ['package' => ':id']))
                ->confirm(['message' => __p('app::phrase.inactive_package_confirm_desc')]);

            $actions->add('batchActive')
                ->asPatch()
                ->asFormDialog(false)
                ->apiUrl('admincp/app/package/batch-active?id=[:id]&active=1')
                ->confirm(['message' => __p('app::phrase.batch_active_package_confirm_desc')]);

            $actions->add('batchInactive')
                ->asPatch()
                ->asFormDialog(false)
                ->apiUrl('admincp/app/package/batch-active?id=[:id]&active=0')
                ->confirm(['message' => __p('app::phrase.batch_inactive_package_confirm_desc')]);

            $actions->add('uninstall')
                ->apiUrl(apiUrl('admin.app.package.uninstall', ['package' => ':id']))
                ->asPatch()
                ->confirm(['message' => __p('app::phrase.uninstall_package_confirm')]);

            $actions->add('install')
                ->apiUrl(apiUrl('admin.app.package.install', ['package' => ':id']))
                ->asPatch()
                ->confirm(['message' => __p('app::phrase.install_package_confirm_desc')]);

            $actions->add('destroy')
                ->apiUrl(apiUrl('admin.app.package.destroy', ['package' => ':id']))
                ->confirm(['message' => __p('app::phrase.delete_package_confirm')]);

            $actions->add('export')
                ->downloadUrl(apiUrl('admin.app.package.export', ['package' => ':id'], true));
        });

        $this->withBatchMenu(function (BatchActionMenu $menu) {
            $menu->addItem('batchActive')
                ->action('batchActive')
                ->icon('ico-toggle-right')
                ->label(__p('core::phrase.is_active'))
                ->reload()
                ->asBatchEdit();
            $menu->addItem('batchInactive')
                ->icon('ico-toggle-left')
                ->action('batchInactive')
                ->label(__p('core::phrase.inactive'))
                ->reload()
                ->asBatchEdit();
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('export')
                ->icon('ico-cloud-down-alt-o')
                ->label(__p('app::phrase.export'))
                ->value(MetaFoxForm::ACTION_ROW_DOWNLOAD)
                ->params(['action' => 'export'])
                ->showWhen(['and', ['eq', 'setting.app.env', 'local']]);

            $menu->addItem('uninstall')
                ->icon('ico-trash-o')
                ->value(MetaFoxForm::ACTION_ROW_DELETE)
                ->label(__p('app::phrase.uninstall'))
                ->action('uninstall')
                ->confirm(['message' => __p('app::phrase.uninstall_package_confirm')])
                ->showWhen([
                    'and', ['falsy', 'item.is_core'], ['falsy', 'item.is_active'], ['truthy', 'item.is_installed'],
                ]);

            $menu->addItem('install')
                ->icon('ico-trash')
                ->value(MetaFoxForm::ACTION_ROW_DELETE)
                ->label(__p('app::phrase.install'))
                ->action('install')
                ->confirm(['message' => __p('app::phrase.install_package_confirm_desc')])
                ->showWhen([
                    'and', ['falsy', 'item.is_core'], ['falsy', 'item.is_installed'],
                ]);

            $menu->withDelete()
                ->label(__p('app::phrase.delete'))
                ->showWhen([
                    'and', ['falsy', 'item.is_core'], ['falsy', 'item.is_installed'],
                ])
                ->confirm(['message' => __p('app::phrase.delete_package_confirm')]);
        });
    }
}
