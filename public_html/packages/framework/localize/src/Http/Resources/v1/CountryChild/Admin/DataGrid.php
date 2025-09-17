<?php

namespace MetaFox\Localize\Http\Resources\v1\CountryChild\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Form\Html\BuiltinAdminSearchForm;
use MetaFox\Localize\Models\Country;
use MetaFox\Localize\Repositories\CountryRepositoryInterface;
use MetaFox\Platform\Resource\Actions;
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
    protected string $appName = 'localize';

    protected string $resourceName = 'country.child';

    protected ?Country $country = null;

    protected function initialize(): void
    {
        $this->setSearchForm(new BuiltinAdminSearchForm());

        $this->addColumn('name')
            ->header(__p('core::phrase.name'))
            ->linkTo('url')
            ->truncateLines()
            ->flex();

        $this->addColumn('state_iso')
            ->header(__p('localize::country.state_iso'))
            ->alignCenter()
            ->width(200);

        $this->addColumn('state_code')
            ->header(__p('localize::country.state_code'))
            ->alignCenter()
            ->width(200);

        $this->addColumn('country_iso')
            ->header(__p('core::phrase.country_iso'))
            ->alignCenter()
            ->width(200);

        $this->addColumn('fips_code')
            ->header(__p('localize::country.fips_code'))
            ->alignCenter()
            ->width(200);

        $this->addColumn('geonames_code')
            ->header(__p('localize::country.geonames_code'))
            ->alignCenter()
            ->width(200);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->add('addItem')
                ->apiUrl(apiUrl('admin.localize.country.child.create', ['country_id' => $this->country?->entityId() ?: 0]));

            $actions->add('addCity')
                ->apiUrl(apiUrl('admin.localize.country.city.create'))
                ->apiParams(['state_id' => ':id']);

            $actions->add('editItem')
                ->apiUrl('admincp/core/form/localize.country_state.update/:id');

            $actions->add('deleteItem')
                ->apiUrl('admincp/localize/country/child/:id');
        });

        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('addCity')
                ->value(MetaFoxForm::ACTION_ROW_EDIT)
                ->label(__p('localize::phrase.add_new_city'))
                ->params([
                    'action' => 'addCity',
                ]);

            $menu->withEdit()
                ->params(['action' => 'editItem']);

            $menu->withDelete()
                ->params(['action' => 'deleteItem']);
        });
    }

    public function boot(int $parentId = 0): void
    {
        if (!$parentId) {
            return;
        }

        $this->country = resolve(CountryRepositoryInterface::class)->find($parentId);

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate(__p('localize::phrase.add_new_state'));
        });
    }
}
