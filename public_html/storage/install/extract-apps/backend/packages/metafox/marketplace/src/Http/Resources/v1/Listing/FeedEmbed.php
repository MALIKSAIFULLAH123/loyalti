<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Listing;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Marketplace\Policies\ListingPolicy;
use MetaFox\Marketplace\Support\Facade\Listing as Facade;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class ListingEmbed.
 * @property Listing $resource
 */
class FeedEmbed extends ListingDetail
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        $item = $this->resource;

        $context = user();

        $canViewListing = policy_check(ListingPolicy::class, 'view', $context, $item);

        $postOnOther   = $this->resource->userId() != $this->resource->ownerId();

        $ownerResource = null;

        if ($postOnOther && null !== $this->resource->ownerEntity) {
            $ownerResource = ResourceGate::user($this->resource->ownerEntity);
        }

        return [
            'id'                => $item->entityId(),
            'module_name'       => $this->getModuleName(),
            'resource_name'     => $item->entityType(),
            'title'             => ban_word()->clean($item->title),
            'categories'        => $this->getCategories(),
            'short_description' => ban_word()->clean($this->getShortDescription()),
            'description'       => $this->getDescription(),
            'price'             => $this->getUserPrice(),
            'image'             => $item->images,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'parent_user'       => $ownerResource,
            'info'              => 'added_a_marketplace',
            'is_show_location'  => false,
            'location'          => $item->toLocationObject(),
            'privacy'           => $item->privacy,
            'tags'              => $this->getTopics(),
            'link'              => !$canViewListing || $item->trashed() ? null : $item->toLink(),
            'url'               => !$canViewListing || $item->trashed() ? null : $item->toUrl(),
            'router'            => !$canViewListing || $item->trashed() ? null : $item->toRouter(),
            'is_featured'       => $item->is_featured,
            'is_sponsor'        => $item->is_sponsor,
            'is_pending'        => !$item->is_approved,
            'is_expired'        => $this->resource->is_expired,
            'is_sold'           => $this->resource->is_sold,
            'is_free'           => $this->isFree(),
            'statistic'         => $this->getStatistic(),
            'expires_label'     => Facade::getExpiredLabel($this->resource, $this->isListing()),
            'creation_date'     => Carbon::parse($this->resource->created_at)->toISOString(),
            'modification_date' => Carbon::parse($this->resource->updated_at)->toISOString(),
        ];
    }

    protected function isListing(): bool
    {
        return true;
    }
}
