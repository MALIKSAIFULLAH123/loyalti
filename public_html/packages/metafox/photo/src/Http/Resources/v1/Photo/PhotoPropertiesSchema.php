<?php

namespace MetaFox\Photo\Http\Resources\v1\Photo;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Photo\Models\Photo;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\User\Http\Resources\v1\User\UserPropertiesSchema;

/**
 * Class PhotoPropertiesSchema.
 * @property ?Photo $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PhotoPropertiesSchema extends JsonResource
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

        $album           = new UserPropertiesSchema($this->resource?->album);
        $albumProperties = Arr::dot($album->toArray($request), 'album_');
        $albumProperties = Arr::undot($albumProperties);

        if (!$this->resource instanceof Photo) {
            return array_merge($this->resourcesDefault(), $userProperties, $albumProperties);
        }

        $content = null;

        $text = $shortDescription = null;

        $reactItem = $this->resource->reactItem();

        if (null !== $this->resource->content) {
            $content = $this->resource->content;
        }

        if ($this->resource->group_id > 0 && null === $content) {
            $content = $reactItem->content;
        }

        if (null !== $content) {
            app('events')->dispatch('core.parse_content', [$this->resource, &$content]);

            $shortDescription = parse_output()->getDescription($content);

            $text = parse_output()->parse($content, true);

            $text = $this->parseHashtags($text);
        }

        $fileItem = $this->resource->fileItem;

        return array_merge([
            'id'                  => $this->resource->entityId(),
            'title'               => ban_word()->clean($this->resource->title),
            'description'         => ban_word()->clean($shortDescription),
            'text'                => $text,
            'width'               => $fileItem->width ?? 0,
            'height'              => $fileItem?->height ?? 0,
            'file_size'           => $fileItem?->file_size ?? 0,
            'mature'              => $this->resource->mature,
            'image'               => $this->resource->image,
            'images'              => is_array($this->resource->images) ? array_values($this->resource->images) : null,
            'creation_date'       => Carbon::parse($this->resource->created_at)->format('c'),
            'modification_date'   => Carbon::parse($this->resource->updated_at)->format('c'),
            'link'                => $this->resource->toLink(),
            'url'                 => $this->resource->toUrl(),
            'slug'                => $this->resource->toSlug(),
            'total_like'          => $reactItem instanceof HasTotalLike ? $reactItem->total_like : 0,
            'total_view'          => $this->resource->total_view,
            'total_share'         => $this->resource->total_share,
            'total_comment'       => $reactItem instanceof HasTotalComment ? $reactItem->total_comment : 0,
            'total_reply'         => $reactItem instanceof HasTotalCommentWithReply ? $reactItem->total_reply : 0,
            'total_download'      => $this->resource->total_download,
            'credit_text'         => config('app.name'),
            'license_url'         => config('app.url') . '/term-of-use',
            'acquire_license_url' => config('app.url') . '/policy',
        ], $userProperties, $albumProperties);
    }

    protected function resourcesDefault(): array
    {
        return [
            'id'                  => null,
            'title'               => null,
            'description'         => null,
            'text'                => null,
            'width'               => null,
            'height'              => null,
            'file_size'           => null,
            'mature'              => null,
            'image'               => null,
            'images'              => null,
            'creation_date'       => null,
            'modification_date'   => null,
            'link'                => null,
            'url'                 => null,
            'slug'                => null,
            'total_like'          => null,
            'total_view'          => null,
            'total_share'         => null,
            'total_comment'       => null,
            'total_reply'         => null,
            'total_download'      => null,
            'credit_text'         => null,
            'license_url'         => null,
            'acquire_license_url' => null,
        ];
    }
}
