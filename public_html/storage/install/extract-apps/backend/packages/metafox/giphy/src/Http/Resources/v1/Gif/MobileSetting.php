<?php

namespace MetaFox\Giphy\Http\Resources\v1\Gif;

use MetaFox\Giphy\Supports\Helpers;
use MetaFox\Platform\Resource\MobileSetting as ResourceSetting;

class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewTrending')
            ->apiUrl('giphy/gif/trending')
            ->apiParams([
                'q'      => ':q',
                'limit'  => Helpers::DEFAULT_LIMIT,
                'rating' => ':rating',
                'bundle' => ':bundle',
            ])
            ->apiRules([
                'q'      => ['truthy', 'q'],
                'limit'  => ['truthy', 'limit'],
                'rating' => ['includes', 'rating', Helpers::GIPHY_RATINGS],
                'bundle' => ['includes', 'bundle', Helpers::GIPHY_BUNDLES],
            ]);

        $this->add('searchForm')
            ->apiUrl('core/mobile/form/giphy.gif.search_form');

        $this->add('viewSearch')
            ->apiUrl('giphy/gif/search')
            ->apiParams([
                'q'      => ':q',
                'limit'  => Helpers::DEFAULT_LIMIT,
                'offset' => ':offset',
                'rating' => ':rating',
                'bundle' => ':bundle',
            ])
            ->apiRules([
                'q'      => ['truthy', 'q'],
                'limit'  => ['truthy', 'limit'],
                'rating' => ['includes', 'rating', Helpers::GIPHY_RATINGS],
                'bundle' => ['includes', 'bundle', Helpers::GIPHY_BUNDLES],
            ]);
    }
}
