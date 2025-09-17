<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Sticker\Http\Resources\v1\Sticker;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_admin_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewRecentSticker')
            ->apiUrl(apiUrl('sticker.recent.show'));

        $this->add('viewByStickerSet')
            ->apiUrl(apiUrl('sticker.index'))
            ->apiParams([
                'set_id' => ':id',
            ]);
    }
}
