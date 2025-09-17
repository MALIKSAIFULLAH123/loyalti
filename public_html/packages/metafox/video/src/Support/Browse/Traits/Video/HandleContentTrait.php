<?php

namespace MetaFox\Video\Support\Browse\Traits\Video;

trait HandleContentTrait
{
    public function handleContentForUpload(): ?string
    {
        if (null !== $this->resource->content) {
            return $this->resource->content;
        }

        $reactItem = $this->resource->reactItem();

        return $reactItem->content;
    }

    public function handleContentForLink(): ?string
    {
        $videoText = $this->resource->videoText;

        if ($videoText) {
            return $videoText->text_parsed;
        }

        return null;
    }
}
