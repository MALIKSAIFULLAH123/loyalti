<?php

namespace MetaFox\FFMPEG\Contracts;

use FFMpeg\Format\Video\DefaultVideo;
use FFMpeg\Format\Audio\DefaultAudio;

/**
 * @mixin DefaultVideo
 */
interface CustomCommandFormat
{
    /**
     * Checking if this format will generate the command by itself.
     *
     * @return bool
     */
    public function isCustomized(): bool;

    /**
     * Toggle customized command or use default command.
     *
     * @param  bool $enable
     * @return self
     */
    public function customized(bool $enable = true): self;
}
