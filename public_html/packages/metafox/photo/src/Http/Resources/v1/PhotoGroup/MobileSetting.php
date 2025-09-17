<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Photo\Http\Resources\v1\PhotoGroup;

use MetaFox\Platform\Resource\MobileSetting as ResourceSetting;
use MetaFox\Platform\Support\Browse\Browse;

/**
 *--------------------------------------------------------------------------
 * Photo Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 * @preload
 * @driverType resource-mobile
 * @driverName photo
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewItem')
            ->apiUrl('photo-set/:id');

        $this->add('viewDetailMediaItem')
            ->apiUrl('photo-set/:id/items')
            ->apiParams([
                'media_id' => ':media_id'
            ]);
    }
}
