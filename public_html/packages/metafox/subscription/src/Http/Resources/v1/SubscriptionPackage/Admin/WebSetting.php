<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\Admin;

use MetaFox\Platform\Resource\WebSetting as Setting;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Subscription\Support\Helper;

/**
 * stub: /packages/resources/resource_admin_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting
 * Inject this class into property $resources.
 * @link \MetaFox\Subscription\Http\Resources\v1\WebAppSetting::$resources;
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class WebSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('admincp/subscription-package')
            ->apiRules([
                'view' => [
                    'includes',
                    'view',
                    [Helper::VIEW_ADMINCP, Browse::VIEW_SEARCH],
                ],
                'q' => [
                    'truthy',
                    'q',
                ],
                'status' => [
                    'includes',
                    'status',
                    Helper::getItemStatus(),
                ],
            ]);

        $this->add('addItem')
            ->apiUrl('admincp/subscription-package/form');

        $this->add('editItem')
            ->apiUrl('admincp/subscription-package/form/:id');

        $this->add('deleteItem')
            ->apiUrl('admincp/subscription-package/:id')
            ->asDelete();

        $this->add('searchItem')
            ->pageUrl('admincp/subscription/search')
            ->placeholder(__p('subscription::admin.search_packages'));

        $this->add('popularItem')
            ->apiUrl('admincp/subscription-package/popular/:id')
            ->asPatch()
            ->apiParams([
                'is_popular' => 1,
            ]);

        $this->add('activeItem')
            ->apiUrl('admincp/subscription-package/active/:id')
            ->asPatch();

        $this->add('searchForm')
            ->apiUrl('admincp/core/form/subscription.package.search_form');
    }
}
