<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointPackage;

use MetaFox\Platform\Resource\MobileSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class MobileSetting
 * Inject this class into property $resources.
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('searchItem')
            ->apiUrl('/activitypoint-package')
            ->placeholder(__p('activitypoint::phrase.search_point_packages'));

        $this->add('homePage')
            ->pageUrl('/activitypoint');

        $this->add('viewAll')
            ->apiUrl('/activitypoint/package')
            ->asGet();
    }
}
