<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Like\Http\Resources\v1\Like;

use MetaFox\Platform\Resource\MobileSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * Blog Mobile Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class MobileSetting.
 * @ignore
 * @codeCoverageIgnore
 * @driverType resource-mobile
 * @driverName like
 */
class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('getReactedList')
            ->apiUrl('preaction/get-reacted-lists')
            ->apiParams([
                'item_id'   => ':item_id',
                'item_type' => ':item_type',
                'react_id'  => ':react_id',
            ]);
    }
}
