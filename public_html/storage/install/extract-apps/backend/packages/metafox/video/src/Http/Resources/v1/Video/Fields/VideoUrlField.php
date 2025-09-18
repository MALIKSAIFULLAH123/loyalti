<?php

namespace MetaFox\Video\Http\Resources\v1\Video\Fields;

use MetaFox\Form\Html\Text;

class VideoUrlField extends Text
{
    public const COMPONENT_NAME = 'VideoUrl';

    public function initialize(): void
    {
        parent::initialize();

        $this->component(self::COMPONENT_NAME)
            ->name('video_url')
            ->returnKeyType('next')
            ->label(__p('video::phrase.video_url'))
            ->placeholder(__p('video::phrase.paste_your_video_url_here'));
    }

    /**
     * @param  array<string, string> $value
     * @return $this
     */
    public function autoFillValue(array $value): self
    {
        return $this->setAttribute('autoFillValueFromLink', $value);
    }

    public function fetchEndpoint(string $endpoint): self
    {
        return $this->setAttribute('fetchEndpoint', $endpoint);
    }

    public function showEmbed(bool $value = true): self
    {
        return $this->setAttribute('showEmbed', $value);
    }
}
