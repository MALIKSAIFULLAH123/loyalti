<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Sticker\Http\Resources\v1\Sticker;

use MetaFox\Platform\Resource\MobileSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * Saved Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class MobileSetting.
 */
class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewRecentSticker')
            ->apiUrl('sticker/recent');

        $this->add('viewByStickerSet')
            ->apiUrl(apiUrl('sticker.index'))
            ->apiParams([
                'set_id' => ':id',
            ]);
    }
}
