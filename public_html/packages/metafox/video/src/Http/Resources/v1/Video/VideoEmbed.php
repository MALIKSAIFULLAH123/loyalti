<?php

namespace MetaFox\Video\Http\Resources\v1\Video;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;
use MetaFox\Video\Http\Resources\v1\Video\Traits\HasExtra as VideoHasExtra;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Support\Browse\Traits\Video\HandleContentTrait;
use MetaFox\Video\Support\Facade\Video as VideoFacade;

/**
 * Class VideoEmbed.
 * @property Video $resource
 */
class VideoEmbed extends JsonResource
{
    use HasHashtagTextTrait;
    use HasStatistic;
    use HandleContentTrait;
    use VideoHasExtra;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $context = user();
        $content = match ($this->resource->group_id > 0) {
            true  => $this->handleContentForUpload(),
            false => $this->handleContentForLink(),
        };

        if ($content) {
            $modelContent = $this->resource->group_id > 0 ? $this->resource->group : $this->resource;
            app('events')->dispatch('core.parse_content', [$modelContent, &$content]);
        }

        if ($this->resource->group_id == 0) {
            if (is_string($content)) {
                $content = $this->getTransformContent($content);
                $content = parse_output()->parseItemDescription($content);
            }
        }

        $description = parse_output()->getDescription($content);

        $mature = $this->resource->mature;

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->moduleName(),
            'resource_name'     => $this->resource->entityType(),
            'is_processing'     => $this->resource->is_processing,
            'is_success'        => $this->resource->is_success,
            'is_failed'         => $this->resource->is_failed,
            'image'             => VideoFacade::getDataWithContext($context, $this->resource),
            'destination'       => VideoFacade::getDataWithContext($context, $this->resource, 'video_path'),
            'title'             => ban_word()->clean($this->resource->title),
            'description'       => $description,
            'embed_code'        => $this->resource->embed_code,
            'video_url'         => VideoFacade::getDataWithContext($context, $this->resource, 'video_url'),
            'duration'          => $this->resource->duration,
            'resolution_x'      => $this->resource->resolution_x,
            'resolution_y'      => $this->resource->resolution_y,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'owner'             => ResourceGate::user($this->resource->ownerEntity),
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'slug'              => $this->resource->toSlug(),
            'is_featured'       => $this->resource->is_featured,
            'is_sponsor'        => $this->resource->is_sponsor,
            'is_sponsored_feed' => $this->resource->sponsor_in_feed,
            'statistic'         => $this->getStatistic(),
            'text'              => $content,
            'mature'            => $mature,
            'tagged_friends'    => [], // Do not remove this. Since videos do not item tagged friends => always return empty array to normalize data for client
            'mature_config'     => VideoFacade::getMatureDataConfig($context, $this->resource),
            'extra'             => $this->getExtra(),
        ];
    }
}
