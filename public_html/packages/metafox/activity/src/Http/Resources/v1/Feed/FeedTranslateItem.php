<?php

namespace MetaFox\Activity\Http\Resources\v1\Feed;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Activity\Traits\HasTagTrait;

class FeedTranslateItem extends JsonResource
{
    use HasTagTrait;

    public function toArray($request)
    {
        $content                 = $this->resource->content;
        $this->resource->content = $this->resource->translated_content;
        $status                  = $this->getParsedContent();

        return [
            'origin_text'     => ban_word()->clean($content),
            'translated_text' => ban_word()->clean($status),
            'target'          => $this->resource->target,
        ];
    }
}
