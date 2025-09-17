<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Sticker\Http\Resources\v1\StickerSet;

use MetaFox\Platform\Resource\MobileSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * Saved Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 */
class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('sticker/sticker-set');

        $this->add('viewMyStickerSet')
            ->apiUrl('sticker/sticker-set')
            ->apiParams(['view' => 'my']);

        $this->add('addToMyList')
            ->apiUrl('sticker/sticker-set/user')
            ->apiParams([
                'id' => ':id',
            ])
            ->asPost();

        $this->add('removeFromMyList')
            ->apiUrl('sticker/sticker-set/user/:id');

        $this->add('viewItem')
            ->apiUrl('sticker/sticker-set/:id')
            ->asGet();
    }
}
