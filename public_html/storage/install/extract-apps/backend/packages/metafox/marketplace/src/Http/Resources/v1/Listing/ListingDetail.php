<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Listing;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Enumerable;
use MetaFox\Core\Http\Resources\v1\Attachment\AttachmentItemCollection;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Marketplace\Http\Resources\v1\Category\CategoryItemCollection;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Marketplace\Support\Browse\Traits\Listing\ExtraTrait;
use MetaFox\Marketplace\Support\Facade\Listing as Facade;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityDetail;

/**
 * Class ListingDetail.
 *
 * @property Listing $resource
 */
class ListingDetail extends JsonResource
{
    use ExtraTrait;
    use HasStatistic;
    use HasFeedParam;
    use IsLikedTrait;
    use IsFriendTrait;
    use HasHashtagTextTrait;

    /**
     * @return array<string, mixed>
     */
    public function getStatistic(): array
    {
        return [
            'total_like'       => $this->resource->total_like,
            'total_view'       => $this->resource->total_view,
            'total_share'      => $this->resource->total_share,
            'total_comment'    => $this->resource->total_comment,
            'total_reply'      => $this->resource->total_reply,
            'total_attachment' => $this->resource->total_attachment,
        ];
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $context = user();

        return [
            'id'                   => $this->resource->entityId(),
            'resource_name'        => $this->resource->entityType(),
            'module_name'          => $this->getModuleName(),
            'title'                => ban_word()->clean($this->resource->title),
            'description'          => $this->getDescription(),
            'short_description'    => ban_word()->clean($this->getShortDescription()),
            'attach_photos'        => $this->getAttachedPhotos(),
            'image'                => $this->resource->images,
            'is_pending'           => !$this->resource->is_approved,
            'is_sponsor'           => $this->resource->is_sponsor,
            'is_featured'          => $this->resource->is_featured,
            'allow_payment'        => $this->resource->allow_payment,
            'is_sold'              => $this->resource->is_sold,
            'is_expired'           => $this->resource->is_expired,
            'is_sponsored_feed'    => $this->resource->sponsor_in_feed,
            'is_saved'             => $this->isSaved(),
            'is_free'              => $this->isFree(),
            'has_sold_invoice'     => $this->resource->hasSoldInvoice($context),
            'price'                => $this->getUserPrice(),
            'categories'           => $this->getCategories(),
            'attachments'          => $this->getAttachments(),
            'link'                 => $this->resource->toLink(),
            'url'                  => $this->resource->toUrl(),
            'router'               => $this->resource->toRouter(),
            'privacy'              => $this->resource->privacy,
            'statistic'            => $this->getStatistic(),
            'user'                 => ResourceGate::user($this->resource->userEntity),
            'owner'                => ResourceGate::user($this->resource->ownerEntity),
            'owner_type_name'      => __p_type_key($this->resource->ownerType()),
            'location'             => $this->resource->toLocationObject(),
            'tags'                 => $this->getTopics(),
            'expires_label_detail' => Facade::getExpiredLabel($this->resource, $this->isListing()),
            'creation_date'        => $this->toCreationDate(),
            'modification_date'    => $this->toModificationDate(),
            'external_link'        => $this->resource->external_link,
            'extra'                => $this->getExtra(),
            'feed_param'           => $this->getFeedParams(),
            'info'                 => 'added_a_marketplace',
            'is_show_location'     => false,
        ];
    }

    protected function isFree(): bool
    {
        $context = user();

        return Facade::isFree($context, $this->resource->price);
    }

    protected function isSaved(): bool
    {
        $context = user();

        return PolicyGate::check($this->resource->entityType(), 'isSavedItem', [$context, $this->resource]);
    }

    protected function getUserPrice(): ?string
    {
        $prices = $this->resource->price;

        $context = user();

        return Facade::getUserPriceFormat($context, $prices);
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

            $text = parse_output()->parse($text);
        }

        return $text;
    }

    protected function getAttachedPhotos(): ?Enumerable
    {
        $attachedPhotos = null;

        if ($this->resource->photos->count()) {
            $attachedPhotos = $this->resource->photos->map(function ($photo) {
                return ResourceGate::asItem($photo, null);
            });
        }

        return $attachedPhotos;
    }

    protected function getCategories(): ResourceCollection
    {
        return new CategoryItemCollection($this->resource->categories);
    }

    protected function getAttachments(): ResourceCollection
    {
        return new AttachmentItemCollection($this->resource->attachments);
    }

    protected function getUserEntity(): UserEntityDetail
    {
        return new UserEntityDetail($this->resource->userEntity);
    }

    protected function getOwnerEntity(): UserEntityDetail
    {
        return new UserEntityDetail($this->resource->ownerEntity);
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

    protected function isListing(): bool
    {
        return false;
    }

    protected function getTopics(): ?array
    {
        $context = user();

        if (!Facade::enableTopic($context, $this->resource->owner)) {
            return null;
        }

        return $this->resource->tags;
    }
}
