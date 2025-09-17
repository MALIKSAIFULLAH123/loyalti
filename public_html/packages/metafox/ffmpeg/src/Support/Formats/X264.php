<?php

namespace MetaFox\FFMPEG\Support\Formats;

use FFMpeg\Format\Video\X264 as Base;
use MetaFox\FFMPEG\Contracts\CustomCommandFormat;

class X264 extends Base implements CustomCommandFormat
{
    protected bool $customized = false;

    public function isCustomized(): bool
    {
        return $this->customized;
    }

    public function customized(bool $enable = true): self
    {
        $this->customized = $enable;

        return $this;
    }
}
