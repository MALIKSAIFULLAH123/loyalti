<?php

namespace MetaFox\Group\Http\Resources\v1\Group;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Group\Models\Group;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Platform\Http\Resources\v1\Category\CategoryPropertiesSEO;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Http\Resources\v1\User\UserPropertiesSchema;
use MetaFox\User\Support\Facades\User;

/**
 * Class GroupPropertiesSchema.
 * @property ?Group $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class GroupPropertiesSchema extends JsonResource
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

        if (!$this->resource instanceof Group) {
            return array_merge($this->resourcesDefault(), $userProperties, $categoryProperties);
        }

        $groupText        = $this->resource->groupText;
        $shortDescription = $text = MetaFoxConstant::EMPTY_STRING;

        if ($groupText) {
            $text             = parse_output()->parseUrl($groupText->text_parsed);
            $shortDescription = parse_output()->parseUrl($groupText->text_parsed);
        }

        return array_merge([
            'id'                => $this->resource->entityId(),
            'title'             => ban_word()->clean($this->resource->name),
            'full_name'         => ban_word()->clean($this->resource->name),
            'privacy'           => $this->resource->privacy,
            'reg_method'        => $this->resource->privacy_type,
            'text'              => $text,
            'description'       => $shortDescription,
            'cover'             => $this->resource->cover,
            'latitude'          => $this->resource->location_latitude,
            'longitude'         => $this->resource->location_longitude,
            'location_name'     => $this->resource->location_name,
            'location_address'  => $this->resource->location_address,
            'item_type'         => $this->resource->entityType(),
            'external_link'     => $this->resource->external_link,
            'short_name'        => User::getShortName(ban_word()->clean($this->resource->name)),
            'phone'             => $this->resource->phone,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'creation_date'     => Carbon::parse($this->resource->created_at)->format('c'),
            'modification_date' => Carbon::parse($this->resource->updated_at)->format('c'),
            'profile_name'      => $this->resource->profile_name,
            'total_member'      => $this->resource->total_member,
        ], $userProperties, $categoryProperties);
    }

    protected function resourcesDefault(): array
    {
        return [
            'id'                => null,
            'title'             => null,
            'full_name'         => null,
            'privacy'           => null,
            'reg_method'        => null,
            'text'              => null,
            'description'       => null,
            'cover'             => null,
            'latitude'          => null,
            'longitude'         => null,
            'location_name'     => null,
            'location_address'  => null,
            'item_type'         => null,
            'short_name'        => null,
            'link'              => null,
            'url'               => null,
            'creation_date'     => null,
            'modification_date' => null,
            'profile_name'      => null,
        ];
    }
}
