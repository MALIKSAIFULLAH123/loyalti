<?php

namespace MetaFox\Video\Http\Resources\v1\Video;

use Carbon\CarbonInterval;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Traits\Helpers\ShareFeedInfoTrait;
use MetaFox\User\Http\Resources\v1\User\UserPropertiesSchema;
use MetaFox\Video\Models\Video as Model;
use MetaFox\Video\Support\Browse\Traits\Video\HandleContentTrait;

/**
 * Class VideoPropertiesSchema.
 * @property ?Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class VideoPropertiesSchema extends JsonResource
{
    use HandleContentTrait;
    use ShareFeedInfoTrait;

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

        $text = match ($this->resource->group_id > 0) {
            true  => $this->handleContentForUpload(),
            false => $this->handleContentForLink(),
        };

        if ($text) {
            $modelContent = $this->resource->group_id > 0 ? $this->resource->group : $this->resource;
            app('events')->dispatch('core.parse_content', [$modelContent, &$text]);
        }

        $shortDescription = $text;

        if ($this->resource->group_id == 0) {
            if (is_string($shortDescription)) {
                $text = $this->getTransformContent($text);
                $text = parse_output()->parseItemDescription($text);
            }
        }

        if (null !== $shortDescription) {
            $shortDescription = parse_output()->getDescription($shortDescription);
        }

        return array_merge([
            'id'                    => $this->resource->entityId(),
            'title'                 => ban_word()->clean($this->resource->title),
            'description'           => $shortDescription,
            'duration'              => $this->resource->duration,
            'duration_iso'          => CarbonInterval::seconds($this->resource->duration)->spec(),
            'embed_code'            => $this->resource->video_url,
            'image'                 => $this->resource->image,
            'link'                  => $this->resource->toLink(),
            'url'                   => $this->resource->toUrl(),
            'slug'                  => $this->resource->toSlug(),
            'creation_date'         => Carbon::parse($this->resource->created_at)->format('c'),
            'text'                  => $text,
            'destination'           => $this->resource->destination,
            'file_ext'              => $this->resource->file_ext,
            'resolution_x'          => $this->resource->resolution_x,
            'resolution_y'          => $this->resource->resolution_y,
            'modification_date'     => Carbon::parse($this->resource->updated_at)->format('c'),
            'interaction_statistic' => $this->getInteractionStatistic(),
        ], $userProperties);
    }

    protected function getInteractionStatistic(): array
    {
        return [
            [
                '@type'                => 'InteractionCounter',
                'interactionType'      => ['@type' => 'WatchAction'],
                'userInteractionCount' => $this->resource->total_view,
            ], [
                '@type'              => 'InteractionCounter',
                'interactionService' => [
                    '@type' => 'WebSite',
                    'name'  => config('app.name'),
                    '@id'   => config('app.url'),
                ],
                'interactionType'      => 'https://schema.org/LikeAction',
                'userInteractionCount' => $this->resource->total_like,
            ],
        ];
    }

    protected function resourcesDefault(): array
    {
        return [
            'id'                    => null,
            'title'                 => null,
            'description'           => null,
            'duration'              => null,
            'duration_iso'          => null,
            'embed_code'            => null,
            'image'                 => null,
            'link'                  => null,
            'url'                   => null,
            'slug'                  => null,
            'creation_date'         => null,
            'text'                  => null,
            'destination'           => null,
            'file_ext'              => null,
            'resolution_x'          => null,
            'resolution_y'          => null,
            'modification_date'     => null,
            'interaction_statistic' => null,
        ];
    }
}
