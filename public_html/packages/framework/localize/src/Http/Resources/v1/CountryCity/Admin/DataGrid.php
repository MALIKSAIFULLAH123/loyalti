<?php

namespace MetaFox\Localize\Http\Resources\v1\CountryCity\Admin;

use MetaFox\Form\Html\BuiltinAdminSearchForm;
use MetaFox\Localize\Models\CountryChild;
use MetaFox\Localize\Repositories\CountryChildRepositoryInterface;
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

    protected string $resourceName = 'country.city';

    protected ?CountryChild $state = null;

    protected function initialize(): void
    {
        $this->setSearchForm(new BuiltinAdminSearchForm());

        $this->addColumn('name')
            ->header(__p('core::phrase.name'))
            ->truncateLines()
            ->flex(2);

        $this->addColumn('city_code')
            ->header(__p('localize::country.city_code'))
            ->flex();

        $this->addColumn('state_code')
            ->header(__p('localize::country.state_code'))
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->add('addItem')
                ->apiUrl(apiUrl('admin.localize.country.city.create', ['state_id' => $this->state?->entityId() ?: 0]));
            $actions->add('edit')
                ->apiUrl(apiUrl('admin.localize.country.city.edit', ['city' => ':id']));
            $actions->add('destroy')
                ->apiUrl(apiUrl('admin.localize.country.city.destroy', ['city' => ':id']))
                ->asDelete();
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
            $menu->withDelete();
        });
    }

    public function boot(int $parentId = 0): void
    {
        if (!$parentId) {
            return;
        }

        $this->state = resolve(CountryChildRepositoryInterface::class)->find($parentId);

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate(__p('localize::phrase.add_new_city'));
        });
    }
}
