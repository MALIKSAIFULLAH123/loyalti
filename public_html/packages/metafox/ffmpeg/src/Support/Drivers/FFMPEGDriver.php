<?php

namespace MetaFox\FFMPEG\Support\Drivers;

use FFMpeg\FFMpeg as BaseDriver;
use InvalidArgumentException;
use MetaFox\FFMPEG\Support\Media\Audio;
use MetaFox\FFMPEG\Support\Media\Video;
use RuntimeException;

class FFMPEGDriver extends BaseDriver
{
    /**
     * Opens a file in order to be processed.
     *
     * @param string $pathfile A pathfile
     *
     * @return Audio|Video
     *
     * @throws InvalidArgumentException
     */
    public function open($pathfile)
    {
        if (null === $streams = $this->getFFProbe()->streams($pathfile)) {
            throw new RuntimeException(sprintf('Unable to probe "%s".', $pathfile));
        }

        if (0 < count($streams->videos())) {
            return new Video($pathfile, $this->getFFMpegDriver(), $this->getFFProbe());
        } elseif (0 < count($streams->audios())) {
            return new Audio($pathfile, $this->getFFMpegDriver(), $this->getFFProbe());
        }

        throw new InvalidArgumentException('Unable to detect file format, only audio and video supported');
    }
}
