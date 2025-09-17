<?php

namespace MetaFox\FFMPEG\Support\Media;

use FFMpeg\Filters\Audio\SimpleFilter;
use FFMpeg\Format\AudioInterface;
use FFMpeg\Format\FormatInterface;
use FFMpeg\Format\VideoInterface;
use FFMpeg\Media\Video as VendorVideo;
use InvalidArgumentException;
use MetaFox\FFMPEG\Contracts\CustomCommandFormat;

class Video extends VendorVideo
{
    /**
     * @inheritDoc
     */
    protected function buildCommand(FormatInterface $format, $outputPathfile)
    {
        if ($format instanceof CustomCommandFormat && $format->isCustomized()) {
            return $this->buildCustomCommands($format, $outputPathfile);
        }

        return parent::buildCommand($format, $outputPathfile);
    }

    protected function buildCustomCommands(FormatInterface $format, $outputPathfile): array
    {
        $commands = $this->basePartOfCommand($format);

        $filters = clone $this->filters;
        $filters->add(new SimpleFilter($format->getExtraParams(), 10));

        if ($this->driver->getConfiguration()->has('ffmpeg.threads')) {
            $filters->add(new SimpleFilter(['-threads', $this->driver->getConfiguration()->get('ffmpeg.threads')]));
        }
        if ($format instanceof VideoInterface) {
            if (null !== $format->getVideoCodec()) {
                $filters->add(new SimpleFilter(['-vcodec', $format->getVideoCodec()]));
            }
        }
        if ($format instanceof AudioInterface) {
            if (null !== $format->getAudioCodec()) {
                $filters->add(new SimpleFilter(['-acodec', $format->getAudioCodec()]));
            }
        }

        foreach ($filters as $filter) {
            $commands = array_merge($commands, $filter->apply($this, $format));
        }

        if ($format instanceof VideoInterface) {
            if (null !== $format->getAdditionalParameters()) {
                foreach ($format->getAdditionalParameters() as $additionalParameter) {
                    $commands[] = $additionalParameter;
                }
            }
        }

        $this->fsId = uniqid('ffmpeg-passes');
        $this->fs   = $this->getTemporaryDirectory()->name($this->fsId)->create();
        $passPrefix = $this->fs->path(uniqid('pass-'));
        touch($passPrefix);
        $passes      = [];
        $totalPasses = $format->getPasses();

        if (!$totalPasses) {
            throw new InvalidArgumentException('Pass number should be a positive value.');
        }

        for ($i = 1; $i <= $totalPasses; $i++) {
            $pass = $commands;

            if ($totalPasses > 1) {
                $pass[] = '-pass';
                $pass[] = $i;
                $pass[] = '-passlogfile';
                $pass[] = $passPrefix;
            }

            $pass[] = $outputPathfile;

            $passes[] = $pass;
        }

        return $passes;
    }
}
