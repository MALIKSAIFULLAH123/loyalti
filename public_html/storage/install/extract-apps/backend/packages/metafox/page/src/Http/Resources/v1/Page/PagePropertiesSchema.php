<?php

namespace MetaFox\Page\Http\Resources\v1\Page;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Page\Models\Page;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Http\Resources\v1\Category\CategoryPropertiesSEO;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Http\Resources\v1\User\UserPropertiesSchema;
use MetaFox\User\Support\Facades\User;

/**
 * Class PagePropertiesSchema.
 * @property ?Page $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PagePropertiesSchema extends JsonResource
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

        $category           = new CategoryPropertiesSEO($this->resource?->category);
        $categoryProperties = Arr::dot($category->toArray($request), 'category_');
        $categoryProperties = Arr::undot($categoryProperties);

        if (!$this->resource instanceof Page) {
            return array_merge($this->resourcesDefault(), $userProperties, $categoryProperties);
        }

        $pageText = $this->resource->pageText;
        $text     = $description = MetaFoxConstant::EMPTY_STRING;

        if ($pageText instanceof ResourceText) {
            $text        = parse_output()->parseUrl($pageText->text_parsed);
            $description = parse_output()->parseUrl($pageText->text_parsed);
        }

        return array_merge([
            'id'                => $this->resource->entityId(),
            'title'             => ban_word()->clean($this->resource->name),
            'full_name'         => ban_word()->clean($this->resource->name),
            'text'              => $text,
            'description'       => $description,
            'external_link'     => $this->resource->external_link,
            'avatar'            => $this->resource->avatar,
            'cover'             => $this->resource->cover,
            'avatars'           => is_array($this->resource->avatars) ? array_values($this->resource->avatars) : null,
            'covers'            => is_array($this->resource->covers) ? array_values($this->resource->covers) : null,
            'latitude'          => $this->resource->location_latitude,
            'longitude'         => $this->resource->location_longitude,
            'profile_name'      => $this->resource->profile_name,
            'location_name'     => $this->resource->location_name,
            'location_address'  => $this->resource->location_address,
            'item_type'         => $this->resource->entityType(),
            'short_name'        => User::getShortName(ban_word()->clean($this->resource->name)),
            'summary'           => $this->resource->summary,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'creation_date'     => Carbon::parse($this->resource->created_at)->format('c'),
            'total_follower'    => $this->resource->total_follower,
            'total_like'        => $this->resource->total_member,
            'modification_date' => Carbon::parse($this->resource->updated_at)->format('c'),
        ], $userProperties, $categoryProperties);
    }

    protected function resourcesDefault(): array
    {
        return [
            'id'                => null,
            'title'             => null,
            'full_name'         => null,
            'text'              => null,
            'description'       => null,
            'external_link'     => null,
            'avatar'            => null,
            'cover'             => null,
            'avatars'           => null,
            'covers'            => null,
            'latitude'          => null,
            'longitude'         => null,
            'location_name'     => null,
            'location_address'  => null,
            'item_type'         => null,
            'short_name'        => null,
            'summary'           => null,
            'link'              => null,
            'url'               => null,
            'creation_date'     => null,
            'modification_date' => null,
            'total_follower'    => null,
            'total_like'        => null,
            'profile_name'      => null,
        ];
    }
}
