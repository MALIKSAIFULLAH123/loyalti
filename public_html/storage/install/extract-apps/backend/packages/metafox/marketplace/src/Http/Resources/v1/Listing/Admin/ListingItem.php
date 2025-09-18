<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Listing\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Carbon;
use MetaFox\Core\Support\Facades\Country;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Marketplace\Http\Resources\v1\Category\CategoryItemCollection;
use MetaFox\Marketplace\Models\Listing as Model;
use MetaFox\Marketplace\Support\Browse\Traits\Listing\ExtraTrait;
use MetaFox\Marketplace\Support\Facade\Listing as Facade;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Facades\Settings;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class ListingItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class ListingItem extends JsonResource
{
    use ExtraTrait;
    use HasHashtagTextTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->resource->entityId(),
            'resource_name'     => $this->resource->entityType(),
            'module_name'       => $this->getModuleName(),
            'title'             => $this->resource->title,
            'description'       => $this->getDescription(),
            'is_pending'        => !$this->resource->is_approved,
            'is_sponsored'      => $this->resource->is_sponsor,
            'is_featured'       => $this->resource->is_featured,
            'is_expired'        => $this->resource->is_expired,
            'is_free'           => $this->isFree(),
            'is_sponsored_feed' => $this->resource->sponsor_in_feed,
            'status_text'       => Facade::getStatusTexts($this->resource),
            'image'             => [
                'url'       => $this->resource->image,
                'file_type' => 'image/*',
            ],
            'country_name'      => $this->resource->country_iso ? Country::getCountryName($this->resource->country_iso) : null,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'user'              => ResourceGate::user($this->resource->userEntity),
            'owner'             => ResourceGate::user($this->resource->ownerEntity),
            'expire_at'         => Carbon::parse($this->resource->start_expired_at)->format('c'),
            'creation_date'     => $this->toCreationDate(),
            'modification_date' => $this->toModificationDate(),
            'extra'             => $this->getExtra(),
        ];
    }

    protected function isFree(): bool
    {
        $context = user();

        return Facade::isFree($context, $this->resource->price);
    }

    protected function getShortDescription(): ?string
    {
        if (!Settings::get('marketplace.enable_short_description_field')) {
            return null;
        }

        $shortText = null;

        if (null !== $this->resource->short_description) {
            $shortText = $this->resource->short_description;
        }

        return $shortText;
    }

    protected function getDescription(): ?string
    {
        $text = null;

        if (null !== $this->resource->marketplaceText) {
            $text = $this->resource->marketplaceText->text_parsed;

            $text = $this->getTransformContent($text);

            $text = parse_output()->getDescription($text);
        }

        return $text;
    }


    protected function getCategories(): ResourceCollection
    {
        return new CategoryItemCollection($this->resource->categories);
    }


    protected function getModuleName(): string
    {
        return 'marketplace';
    }

    protected function toCreationDate(): string
    {
        return Carbon::parse($this->resource->created_at)->format('c');
    }

    protected function toModificationDate(): string
    {
        return Carbon::parse($this->resource->updated_at)->format('c');
    }
}
