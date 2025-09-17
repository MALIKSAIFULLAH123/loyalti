<?php

namespace MetaFox\Platform\Traits\Http\Controllers;

use MetaFox\Platform\Facades\Settings;

trait HasRevisionTrait
{
    /**
     * @param  array  $arr
     * @return string
     */
    public function getLatestRevision(array $arr): string
    {
        $arr[] = Settings::versionId();

        return substr(md5(implode('-', array_map(function (mixed $value) {
            return sprintf('%s', $value);
        }, $arr))), -8);
    }
}
