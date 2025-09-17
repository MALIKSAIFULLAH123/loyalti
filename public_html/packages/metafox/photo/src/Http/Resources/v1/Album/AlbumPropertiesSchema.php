<?php

namespace MetaFox\Photo\Http\Resources\v1\Album;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Photo\Models\Album;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\User\Http\Resources\v1\User\UserPropertiesSchema;

/**
 * Class AlbumPropertiesSchema.
 * @property ?Album $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class AlbumPropertiesSchema extends JsonResource
{
    use HasHashtagTextTrait;

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

        if (!$this->resource instanceof Album) {
            return array_merge($this->resourcesDefault(), $userProperties);
        }

        $description = '';

        $albumText = $this->resource->albumText;

        if ($albumText) {
            $description = $albumText->text_parsed;
        }
        $reactItem = $this->resource->reactItem();

        return array_merge([
            'id'                => $this->resource->id,
            'title'             => ban_word()->clean($this->resource->name),
            'text'              => $albumText?->text ?? '',
            'text_parsed'       => parse_output()->parseItemDescription($description),
            'image'             => $this->resource->image,
            'total_item'        => $this->resource->total_item,
            'creation_date'     => Carbon::parse($this->resource->created_at)->format('c'),
            'modification_date' => Carbon::parse($this->resource->updated_at)->format('c'),
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'total_photo'       => $this->resource->total_photo,
            'total_like'        => $reactItem instanceof HasTotalLike ? $reactItem->total_like : 0,
            'total_share'       => $this->resource->total_share,
        ], $userProperties);
    }

    protected function resourcesDefault(): array
    {
        return [
            'id'                => null,
            'name'              => null,
            'text'              => null,
            'text_parsed'       => null,
            'image'             => null,
            'total_item'        => null,
            'creation_date'     => null,
            'modification_date' => null,
            'link'              => null,
            'url'               => null,
            'total_photo'       => null,
            'total_like'        => null,
            'total_share'       => null,
        ];
    }
}
