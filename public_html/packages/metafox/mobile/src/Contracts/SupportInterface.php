<?php

namespace MetaFox\Mobile\Contracts;

interface SupportInterface
{
    /**
     * @return array
     */
    public function getSmartBannerPositionOptions(): array;

    /**
     * @return array
     */
    public function getAllowSmartBannerPosition(): array;
}
