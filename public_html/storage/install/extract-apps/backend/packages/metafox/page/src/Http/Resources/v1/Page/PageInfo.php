<?php

namespace MetaFox\Page\Http\Resources\v1\Page;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Page\Http\Resources\v1\PageCategory\PageCategoryEmbed;
use MetaFox\Page\Http\Resources\v1\Traits\PageHasExtra;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomProfile;

/**
 * Class PageInfo.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PageInfo extends JsonResource
{
    use PageHasExtra;

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
        $pageText              = $this->resource->pageText;
        $description           = $text = $textParsed = MetaFoxConstant::EMPTY_STRING;
        $additionalInformation = CustomProfile::getProfileValues($this->resource, [
            'section_type' => CustomField::SECTION_TYPE_PAGE,
            'for_form'     => false,
        ]);

        if ($pageText instanceof ResourceText) {
            $description = parse_output()->parseItemDescription($pageText->text_parsed);
            $text        = parse_output()->parseUrl($pageText->text_parsed);
            $textParsed  = parse_output()->parseItemDescription($pageText->text_parsed);
        }

        /*
         * text and description => Support for Mobile
         * text_parsed => Support for Web
         * */
        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->entityType(),
            'resource_name'     => 'page_info',
            'text_parsed'       => $textParsed,
            'text'              => $text,
            'description'       => $description,
            'total_like'        => $this->resource->total_member,
            'external_link'     => $this->resource->external_link,
            'phone'             => null,
            //@todo: In the next version, we will check validation of this field and display it again
            'location'          => $this->resource->location_name,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'extra'             => $this->getExtra(),
            'privacy'           => $this->resource->privacy,
            'category'          => new PageCategoryEmbed($this->resource->category),
            'sections'          => $additionalInformation,
        ];
    }
}
