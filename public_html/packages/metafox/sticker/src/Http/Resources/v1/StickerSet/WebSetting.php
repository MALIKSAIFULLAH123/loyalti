<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Sticker\Http\Resources\v1\StickerSet;

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
        $this->add('viewAll')
            ->apiUrl(apiUrl('sticker.sticker-set.index'));

        $this->add('viewMyStickerSet')
            ->apiUrl(apiUrl('sticker.sticker-set.index'))
            ->apiParams(['view' => 'my']);

        $this->add('addToMyList')
            ->apiUrl(apiUrl('sticker.sticker-set.user.store'))
            ->apiParams([
                'id' => ':id',
            ])
            ->asPost();

        $this->add('removeFromMyList')
            ->apiUrl(apiUrl('sticker.sticker-set.user.destroy', ['id' => ':id']))
            ->asDelete();

        $this->add('viewItem')
            ->apiUrl(apiUrl('sticker.sticker-set.show', ['sticker_set' => ':id']))
            ->asGet();
    }
}
