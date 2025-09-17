<?php

namespace MetaFox\Comment\Http\Resources\v1\Comment;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Comment\Traits\HasTransformContent;

class CommentTranslationItem extends JsonResource
{
    use HasTransformContent;

    public function toArray($request)
    {
        $tmpTextParsed               = $this->resource->text_parsed;
        $this->resource->text_parsed = $this->resource->translated_text;
        $translatedTextParsed        = $this->getTransformContent();
        $this->resource->text_parsed = $tmpTextParsed;

        return [
            'origin_text'     => ban_word()->clean($this->resource->text_parsed),
            'translated_text' => ban_word()->clean($translatedTextParsed),
            'target'          => $this->resource->target,
        ];
    }
}
