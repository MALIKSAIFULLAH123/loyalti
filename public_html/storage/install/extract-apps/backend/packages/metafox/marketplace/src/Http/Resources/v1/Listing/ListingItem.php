<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Listing;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use MetaFox\Marketplace\Models\Listing as Model;
use MetaFox\Marketplace\Support\Facade\Listing;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class ListingItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ListingItem extends ListingDetail
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string,           mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        return [
            'id'                 => $this->resource->entityId(),
            'resource_name'      => $this->resource->entityType(),
            'module_name'        => $this->getModuleName(),
            'title'              => ban_word()->clean($this->resource->title),
            'description'        => $this->getDescription(),
            'short_description'  => ban_word()->clean($this->getShortDescription()),
            'is_pending'         => !$this->resource->is_approved,
            'is_sponsor'         => $this->resource->is_sponsor,
            'is_featured'        => $this->resource->is_featured,
            'allow_payment'      => $this->resource->allow_payment,
            'is_sold'            => $this->resource->is_sold,
            'is_expired'         => $this->resource->is_expired,
            'is_saved'           => $this->isSaved(),
            'is_free'            => $this->isFree(),
            'is_sponsored_feed'  => $this->resource->sponsor_in_feed,
            'price'              => $this->getUserPrice(),
            'image'              => $this->resource->images,
            'attached_photos'    => $this->getAttachedPhotos(),
            'location'           => $this->resource->toLocationObject(),
            'privacy'            => $this->resource->privacy,
            'statistic'          => $this->getStatistic(),
            'link'               => $this->resource->toLink(),
            'url'                => $this->resource->toUrl(),
            'router'             => $this->resource->toRouter(),
            'user'               => ResourceGate::user($this->resource->userEntity),
            'tags'               => $this->getTopics(),
            'expires_label_item' => Listing::getExpiredLabel($this->resource, $this->isListing()),
            'creation_date'      => $this->toCreationDate(),
            'modification_date'  => $this->toModificationDate(),
            'extra'              => $this->getExtra(false),
        ];
    }

    protected function isListing(): bool
    {
        return true;
    }
}
