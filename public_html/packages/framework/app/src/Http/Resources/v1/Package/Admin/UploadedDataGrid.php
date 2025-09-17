<?php

namespace MetaFox\App\Http\Resources\v1\Package\Admin;

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
use MetaFox\App\Http\Resources\v1\Package\Admin\SearchUploadedPackageForm;

/**
 * Class DataGrid.
 * @ignore
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class UploadedDataGrid extends Grid
{
    protected string $appName = 'app';

    protected string $resourceName = 'package';

    protected function initialize(): void
    {
        $this->setSearchForm(new SearchUploadedPackageForm());
        $this->setDataSource(apiUrl('admin.app.package.index'), [
            'q' => ':q',
        ]);

        $this->addColumn('title')
            ->header(__p('core::phrase.title'))
            ->linkTo('internal_admin_url')
            ->flex(1, 30);

        $this->addColumn('type')
            ->header(__p('core::phrase.type'))
            ->flex(1, 30);

        $this->addColumn('version')
            ->header(__p('app::phrase.version'))
            ->flex(1, 30);

        $this->addColumn('author.name')
            ->header(__p('app::phrase.author'))
            ->linkTo('author.url')
            ->target('_blank')
            ->flex(1, 30);

        $this->addColumn('is_core')
            ->header(__p('app::phrase.is_core'))
            ->flex(1, 30)
            ->asYesNoIcon();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['export', 'edit', 'destroy']);

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
                ->confirm(['message' => __p('app::phrase.reinstall_package_confirm_desc')])
                ->showWhen([
                    'and', ['falsy', 'item.is_core'], ['falsy', 'item.is_installed'], ['falsy', 'item.is_force_upgrade'],
                ]);

            $menu->addItem('upgrade')
                ->value(MetaFoxForm::ACTION_ROW_LINK)
                ->label(__p('app::phrase.upgrade'))
                ->params([
                    'to' => '/app/store/product/:store_id',
                ])
                ->showWhen([
                    'and', ['falsy', 'item.is_core'], ['falsy', 'item.is_installed'], ['truthy', 'item.is_force_upgrade'],
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
