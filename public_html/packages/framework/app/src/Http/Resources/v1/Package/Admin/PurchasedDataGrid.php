<?php

namespace MetaFox\App\Http\Resources\v1\Package\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Html\BuiltinAdminSearchForm;
use MetaFox\Platform\Resource\GridConfig as Grid;

/**
 * Class PurchasedDataGrid.
 * @ignore
 */
class PurchasedDataGrid extends Grid
{
    protected string $appName      = 'app';
    protected string $resourceName = 'package';

    protected function initialize(): void
    {
        $this->inlineSearch(['name', 'type', 'author', 'version', 'latest_version']);

        $this->setDataSource(apiUrl('admin.app.package.purchased'));

        $this->setSearchForm(new BuiltinAdminSearchForm());

        $this->addColumn('name')
            ->header(__p('core::phrase.title'))
            ->linkTo('store_app_link')
            ->target('_blank')
            ->flex();

        $this->addColumn('type')
            ->header(__p('core::phrase.type'))
            ->flex(1, 120);

        $this->addColumn('pricing_type')
            ->header(__p('app::phrase.pricing_type'))
            ->flex();

        $this->addColumn('current_version')
            ->header(__p('app::phrase.installed_version'))
            ->alignCenter()
            ->flex(1, 120);

        $this->addColumn('version')
            ->header(__p('app::phrase.upgradable_version'))
            ->alignCenter()
            ->flex(1, 120);

        $this->addColumn('author.name')
            ->header(__p('app::phrase.author'))
            ->alignCenter()
            ->linkTo('author.url')
            ->target('_blank')
            ->flex(1, 120);

        $this->addColumn('is_expired')
            ->header(__p('app::phrase.expired_at'))
            ->asYesNoIcon()
            ->flex(1, 120);
    }
}
