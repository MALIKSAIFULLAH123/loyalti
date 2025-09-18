<?php

namespace MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo;

use Carbon\CarbonInterval;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\LiveStreaming\Models\LiveVideo as Model;
use MetaFox\LiveStreaming\Support\Traits\RepoTrait;
use MetaFox\Platform\Traits\Helpers\ShareFeedInfoTrait;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;
use MetaFox\User\Http\Resources\v1\User\UserPropertiesSchema;

/**
 * Class LiveVideoPropertiesSchema.
 * @property ?Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class LiveVideoPropertiesSchema extends JsonResource
{
    use HasStatistic;
    use ShareFeedInfoTrait;
    use RepoTrait;

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
        $user           = new UserPropertiesSchema($this->resource?->user);
        $userProperties = Arr::dot($user->toArray($request), 'user_');
        $userProperties = Arr::undot($userProperties);

        if (!$this->resource instanceof Model) {
            return array_merge($this->resourcesDefault(), $userProperties);
        }

        $shortDescription = $text = '';
        if ($this->resource->liveVideoText) {
            $shortDescription = parse_output()->getDescription($this->resource->liveVideoText->text);
            $text             = $this->getTransformContent($this->resource->liveVideoText->text);
            $text             = parse_output()->parse($text);
        }

        $repository    = $this->getLiveVideoRepository();
        $thumbnailUrls = $repository->getThumbnailPlayback($this->resource->entityId());

        return array_merge([
            'id'                    => $this->resource->entityId(),
            'title'                 => $this->resource->title ?: __p('livestreaming::phrase.live_video'),
            'duration'              => $this->resource->duration,
            'duration_iso'          => $this->resource->duration ? CarbonInterval::seconds($this->resource->duration)->spec() : null,
            'description'           => $shortDescription,
            'is_streaming'          => $this->resource->is_streaming,
            'is_landscape'          => $this->resource->is_landscape,
            'tags'                  => $this->resource->tags,
            'creation_date'         => Carbon::parse($this->resource->created_at)->format('c'),
            'modification_date'     => Carbon::parse($this->resource->updated_at)->format('c'),
            'link'                  => $this->resource->toLink(),
            'url'                   => $this->resource->toUrl(),
            'playback'              => $this->resource->playback,
            'video_url'             => $repository->getVideoPlayback($this->resource->entityId()),
            'thumbnail_url'         => Arr::get($thumbnailUrls, 'origin', ''),
            'text'                  => $text,
            'interaction_statistic' => $this->getInteractionStatistic(),
        ], $userProperties);
    }

    protected function getInteractionStatistic(): array
    {
        return [
            '@type'                => 'InteractionCounter',
            'interactionType'      => ['@type' => 'WatchAction'],
            'userInteractionCount' => $this->resource->total_view,
        ];
    }

    protected function resourcesDefault(): array
    {
        return [
            'id'                    => null,
            'title'                 => null,
            'duration'              => null,
            'duration_iso'          => null,
            'description'           => null,
            'is_streaming'          => null,
            'is_landscape'          => null,
            'tags'                  => null,
            'creation_date'         => null,
            'modification_date'     => null,
            'link'                  => null,
            'url'                   => null,
            'playback'              => null,
            'video_url'             => null,
            'thumbnail_url'         => null,
            'text'                  => null,
            'interaction_statistic' => null,
        ];
    }
}
