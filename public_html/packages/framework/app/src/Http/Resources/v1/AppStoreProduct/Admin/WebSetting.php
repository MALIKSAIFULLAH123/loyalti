<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\App\Http\Resources\v1\AppStoreProduct\Admin;

use MetaFox\Platform\Resource\WebSetting as Setting;
use MetaFox\App\Support\MetaFoxStore;

/**
 *--------------------------------------------------------------------------
 * Menu Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 * @driverName app_store_product
 */
class WebSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('admincp/app/store/products/browse')
            ->apiParams([
                'q'            => ':q',
                'type'         => ':type',
                'category'     => ':category',
                'price_filter' => ':price_filter',
                'sort'         => ':sort',
                'featured'     => ':featured',
            ])
            ->apiRules([
                'q'            => ['truthy', 'q'],
                'type'         => ['includes', 'type', SearchForm::getAllowedOptions('type')],
                'category'     => ['truthy', 'category', $this->getAllowedCategories()],
                'price_filter' => ['includes', 'price_filter', SearchForm::getAllowedOptions('price_filter')],
                'sort'         => ['includes', 'sort', SearchForm::getAllowedOptions('sort')],
                'featured'     => ['includes', 'featured', SearchForm::getAllowedOptions('featured')],
            ]);

        $this->add('viewItem')
            ->apiUrl('admincp/app/store/product/:id');

        $this->add('getSearchForm')
            ->apiUrl('admincp/app/store/search/form')
            ->asGet();
    }

    /**
     * @return array<string>
     */
    public function getAllowedCategories(): array
    {
        return resolve(MetaFoxStore::class)->getAllowedCategories();
    }
}
