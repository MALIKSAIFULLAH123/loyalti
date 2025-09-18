<?php

namespace MetaFox\Music\Http\Resources\v1\Playlist;

use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Core\Traits\HasDescriptionTrait;
use MetaFox\Music\Models\Playlist;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\User\Http\Resources\v1\User\UserPropertiesSchema;

/**
 * Class PlaylistPropertiesSchema.
 * @property ?Playlist $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PlaylistPropertiesSchema extends JsonResource
{
    use HasDescriptionTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $user           = new UserPropertiesSchema($this->resource?->user);
        $userProperties = Arr::dot($user->toArray($request), 'user_');
        $userProperties = Arr::undot($userProperties);

        if (!$this->resource instanceof Playlist) {
            return array_merge($this->resourcesDefault(), $userProperties);
        }

        $reactItem = $this->resource->reactItem();

        return array_merge([
            'id'                => $this->resource->entityId(),
            'name'              => ban_word()->clean($this->resource->name),
            'description'       => $this->getShortDescription(),
            'text'              => $this->getDescription(),
            'image'             => $this->resource->image,
            'images'            => is_array($this->resource->images) ? array_values($this->resource->images) : null,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'creation_date'     => Carbon::parse($this->resource->created_at)->format('c'),
            'modification_date' => Carbon::parse($this->resource->updated_at)->format('c'),
            'total_like'        => $reactItem instanceof HasTotalLike ? $reactItem->total_like : 0,
            'total_share'       => $this->resource->total_share,
            'total_view'        => $this->resource->total_view,
            'total_song'        => $this->resource->total_track,
            'total_play'        => $this->resource->total_play,
            'total_reply'       => $reactItem instanceof HasTotalCommentWithReply ? $reactItem->total_reply : 0,
            'duration_iso'      => CarbonInterval::seconds($this->resource->total_duration)->spec(),
            'duration'          => $this->resource->total_duration,
        ], $userProperties);
    }

    protected function resourcesDefault(): array
    {
        return [
            'id'                => null,
            'name'              => null,
            'description'       => null,
            'text'              => null,
            'image'             => null,
            'images'            => null,
            'link'              => null,
            'url'               => null,
            'creation_date'     => null,
            'modification_date' => null,
            'total_like'        => null,
            'total_share'       => null,
            'total_view'        => null,
            'total_song'        => null,
            'total_play'        => null,
            'total_reply'       => null,
            'duration_iso'      => null,
            'duration'          => null,
        ];
    }
}
