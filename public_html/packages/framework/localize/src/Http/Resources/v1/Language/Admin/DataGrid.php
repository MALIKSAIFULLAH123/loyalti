<?php

namespace MetaFox\Localize\Http\Resources\v1\Language\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants;
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
    protected string $appName = 'localize';

    protected string $resourceName = 'language';

    protected function initialize(): void
    {
        $this->setDefaultDataSource();

        $this->addColumn('name')
            ->header(__p('core::phrase.name'))
            ->linkTo('links.phrases')
            ->flex();

        $this->addColumn('language_code')
            ->header(__p('app::phrase.language_code'))
            ->alignCenter()
            ->width(200);

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive();

        $this->additionalSection([
            'component'  => 'LanguageSection',
            'title'      => __p('localize::phrase.latest_released_languages'),
            'titleProps' => ['variant' => 'h4'],
            'dataSource' => [
                'apiUrl'    => apiUrl('admin.store.latest', ['type' => 'language']),
                'apiParams' => ['limit' => 6],
                'apiMethod' => 'GET',
            ],
        ]);

        $this->withActions(function (Actions $actions) {
            $actions->addActions(['toggleActive', 'edit', 'destroy']);
            $actions->add('exportPhrases')
                ->downloadUrl(apiUrl('admin.localize.language.exportPhrases', ['ver' => 'v1', 'id' => ':id'], true));
            $actions->add('uploadCSV')
                ->apiUrl(apiUrl('admin.localize.language.uploadCSV', ['ver' => 'v1', 'id' => ':id']));
            $actions->add('findMissingPhrases')
                ->asPatch()
                ->apiUrl(apiUrl('admin.localize.language.phrase.missing', ['ver' => 'v1', 'id' => ':id']));
        });

        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('editItem')
                ->value(Constants::ACTION_ROW_EDIT)
                ->label(__p('core::phrase.edit'))
                ->params(['action' => 'edit']);
            $menu->addItem('findMissingPhrases')
                ->value(Constants::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('localize::phrase.find_missing_phrases'))
                ->params([
                    'action'       => 'findMissingPhrases',
                    'showProgress' => true,
                ])
                ->showWhen([
                    'and',
                    ['neq', 'item.language_code', 'en'],
                ]);
            $menu->addItem('uploadCSV')
                ->value(Constants::ACTION_ROW_EDIT)
                ->label(__p('localize::phrase.import_phrases'))
                ->params(['action' => 'uploadCSV']);
            $menu->addItem('exportPhrases')
                ->value('row/download')
                ->label(__p('localize::phrase.export_phrases'))
                ->params(['action' => 'exportPhrases']);
            $menu->addItem('deleteItem')
                ->value(Constants::ACTION_ROW_DELETE)
                ->label(__p('core::phrase.delete'))
                ->params([
                    'action' => 'destroy',
                ])
                ->confirm(['message' => __p('app::phrase.uninstall_package_confirm')])
                ->showWhen([
                    'and',
                    ['falsy', 'item.is_default'],
                    ['falsy', 'item.is_master'],
                    ['falsy', 'item.is_active'],
                ]);
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('app::phrase.create_language_package'))
                ->removeAttribute('value')
                ->to('localize/language/create');
        });
    }
}
