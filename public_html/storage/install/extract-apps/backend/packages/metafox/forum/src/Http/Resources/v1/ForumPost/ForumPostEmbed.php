<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumPost;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Forum\Http\Resources\v1\ForumThread\ForumThreadEmbed;
use MetaFox\Forum\Models\ForumPostText;
use MetaFox\Forum\Support\Browse\Traits\ForumPost\StatisticTrait;
use MetaFox\Platform\Facades\ResourceGate;

class ForumPostEmbed extends JsonResource
{
    use StatisticTrait;

    public function toArray($request): array
    {
        $resource = $this->resource;

        $content = '';

        $postText = $resource->postText;

        if ($postText instanceof ForumPostText) {
            $content = parse_output()->parseItemDescription($postText->text_parsed);
        }

        $attachments = ResourceGate::items($resource->attachments, false);

        return [
            'id'            => $resource->entityId(),
            'resource_name' => $resource->entityType(),
            'module_name'   => 'forum',
            'user'          => ResourceGate::user($this->resource->userEntity),
            'short_content' => $resource->short_content,
            'is_pending'    => !$this->resource->is_approved,
            'content'       => $content,
            'statistic'     => $this->getStatistic(),
            'creation_date' => Carbon::parse($this->resource->created_at)->format('c'),
            'link'          => $this->resource->toLink(),
            'attachments'   => $attachments,
            'thread'        => new ForumThreadEmbed($this->resource->thread),
        ];
    }
}
