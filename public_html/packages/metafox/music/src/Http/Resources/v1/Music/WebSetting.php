<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Music\Http\Resources\v1\Music;

use MetaFox\Music\Support\Browse\Scopes\Song\SortScope;
use MetaFox\Music\Support\Browse\Scopes\Song\ViewScope;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;

/**
 *--------------------------------------------------------------------------
 * Song Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 * @preload
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('music/search')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'view'        => ':view',
                'genre_id'    => ':genre_id',
                'is_featured' => ':is_featured',
                'entity_type' => ':entity_type',
            ])
            ->apiRules([
                'q'           => ['truthy', 'q'],
                'sort'        => [
                    'includes', 'sort', SortScope::getAllowSort(),
                ],
                'when'        => [
                    'includes', 'when', WhenScope::getAllowWhen(),
                ],
                'view'        => [
                    'includes', 'view', ViewScope::getAllowView(),
                ],
                'genre_id'    => ['truthy', 'genre_id'],
                'is_featured' => ['truthy', 'is_featured'],
                'entity_type' => ['truthy', 'entity_type'],
            ]);
    }
}
