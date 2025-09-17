<?php

namespace MetaFox\Mobile\Supports;

use Illuminate\Support\Arr;
use MetaFox\Mobile\Contracts\SupportInterface;

class Support implements SupportInterface
{
    public const SMART_BANNER_POSITION_BOTTOM = 'bottom';
    public const SMART_BANNER_POSITION_TOP    = 'top';

    public function getSmartBannerPositionOptions(): array
    {
        return [
            [
                'label' => __p('mobile::phrase.smart_banner_position_top'),
                'value' => self::SMART_BANNER_POSITION_TOP,
            ],
            [
                'label' => __p('mobile::phrase.smart_banner_position_bottom'),
                'value' => self::SMART_BANNER_POSITION_BOTTOM,
            ],
        ];
    }

    public function getAllowSmartBannerPosition(): array
    {
        return Arr::pluck($this->getSmartBannerPositionOptions(), 'value');
    }
}
