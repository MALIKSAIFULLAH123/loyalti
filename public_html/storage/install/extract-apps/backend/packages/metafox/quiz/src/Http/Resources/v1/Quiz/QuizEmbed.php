<?php

namespace MetaFox\Quiz\Http\Resources\v1\Quiz;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Quiz\Models\Quiz as Model;

/*
|--------------------------------------------------------------------------
| Resource Embed
|--------------------------------------------------------------------------
|
| Resource embed is used when you want attach this resource as embed content of
| activity feed, notification, ....
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
| @link /app/Console/Commands/stubs/module/resources/detail.stub
*/

/**
 * Class QuizEmbed.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class QuizEmbed extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $shortDescription = '';
        $quizText         = $this->resource->quizText;
        if ($quizText) {
            $shortDescription = parse_output()->getDescription($quizText->text_parsed);
        }

        return [
            'id'            => $this->resource->entityId(),
            'resource_name' => $this->resource->entityType(),
            'module_name'   => $this->resource->entityType(),
            'title'         => ban_word()->clean($this->resource->title),
            'description'   => $shortDescription,
            'image'         => $this->resource->images,
            'privacy'       => $this->resource->privacy,
            'link'          => $this->resource->toLink(),
            'is_sponsor'    => $this->resource->is_sponsor,
            'is_featured'   => $this->resource->is_featured,
            'statistic'     => $this->getStatistic(),
            'attachments'   => ResourceGate::items($this->resource->attachments, false),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getStatistic(): array
    {
        return [
            'total_like'       => $this->resource->total_like,
            'total_play'       => $this->resource->total_play,
            'total_view'       => $this->resource->total_view,
            'total_comment'    => $this->resource->total_comment,
            'total_attachment' => $this->resource->total_attachment,
            'total_share'      => $this->resource->total_share,
        ];
    }
}
