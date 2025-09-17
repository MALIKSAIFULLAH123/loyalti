<?php

namespace MetaFox\Group\Http\Resources\v1\Group;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Http\Resources\v1\Category\CategoryEmbed;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Support\Browse\Traits\Group\StatisticTrait;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomProfile;

/**
 * Class GroupInfo.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class GroupInfo extends JsonResource
{
    use HasExtra;
    use StatisticTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $groupText        = $this->resource->groupText;
        $shortDescription = $text = $textParsed = '';
        $parent           = null;
        if ($groupText) {
            $text       = parse_output()->parseUrl($groupText->text_parsed);
            $textParsed = $shortDescription = parse_output()->parseItemDescription($groupText->text_parsed);
        }

        $category = $this->resource->category;
        if ($category->parent_id != null) {
            $parent = new CategoryEmbed($category->parentCategory);
        }

        $additionalInformation = CustomProfile::getProfileValues($this->resource, [
            'section_type' => CustomField::SECTION_TYPE_GROUP,
            'for_form'     => false,
        ]);

        /*
         * text and description => Support for Mobile
         * text_parsed => Support for Web
         * */
        $data = [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->entityType(),
            'resource_name'     => 'group_info',
            'text'              => $text,
            'text_parsed'       => $textParsed,
            'description'       => $shortDescription,
            'external_link'     => $this->resource->external_link,
            'phone'             => $this->resource->phone,
            'location'          => $this->resource->location_name,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'extra'             => $this->getExtra(),
            'privacy'           => $this->resource->privacy,
            'reg_method'        => $this->resource->privacy_type,
            'reg_name'          => __p(PrivacyTypeHandler::PRIVACY_PHRASE[$this->resource->privacy_type]),
            'category'          => new CategoryEmbed($category),
            'type'              => $parent,
            'sections'          => $additionalInformation,
        ];

        return array_merge($data, $this->getStatistic());
    }
}
