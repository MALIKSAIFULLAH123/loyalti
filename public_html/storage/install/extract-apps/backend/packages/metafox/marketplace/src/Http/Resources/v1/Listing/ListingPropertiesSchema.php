<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Listing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Marketplace\Models\Image;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Marketplace\Support\Facade\Listing as ListingFacade;
use MetaFox\User\Http\Resources\v1\User\UserPropertiesSchema;

/**
 * Class ListingPropertiesSchema.
 * @property ?Listing $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ListingPropertiesSchema extends JsonResource
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

        if (!$this->resource instanceof Listing) {
            return array_merge($this->resourcesDefault(), $userProperties);
        }

        $defaultCurrency = app('currency')->getDefaultCurrencyId();
        $price           = ListingFacade::getPriceByCurrency($defaultCurrency, $this->resource->price);

        return array_merge([
            'id'                 => $this->resource->entityId(),
            'title'              => ban_word()->clean($this->resource->title),
            'description'        => $this->getDescription(),
            'short_description'  => ban_word()->clean($this->getShortDescription()),
            'attach_photos'      => $this->getAttachedPhotos(),
            'price'              => $price,
            'price_currency'     => $defaultCurrency,
            'link'               => $this->resource->toLink(),
            'url'                => $this->resource->toUrl(),
            'router'             => $this->resource->toRouter(),
            'privacy'            => $this->resource->privacy,
            'tags'               => $this->resource->tags,
            'creation_date'      => $this->toCreationDate(),
            'modification_date'  => $this->toModificationDate(),
            'external_link'      => $this->resource->external_link,
            'structure_location' => $this->getStructureLocation(),
        ], $userProperties);
    }

    protected function getStructureLocation(): array
    {
        if (!$this->resource instanceof Listing) {
            return [];
        }

        $address = ["@type" => "PostalAddress"];

        if ($this->resource->country_iso) {
            Arr::set($address, "addressCountry", $this->resource->country_iso);
        }
        if ($this->resource->location_name) {
            Arr::set($address, "streetAddress", $this->resource->location_address ?? $this->resource->location_name);
        }

        return [
            "@type"   => "Place",
            "address" => $address,
        ];
    }

    protected function resourcesDefault(): array
    {
        return [
            'id'                 => null,
            'title'              => null,
            'description'        => null,
            'short_description'  => null,
            'attach_photos'      => null,
            'price'              => null,
            'price_currency'     => null,
            'link'               => null,
            'url'                => null,
            'router'             => null,
            'privacy'            => null,
            'tags'               => null,
            'creation_date'      => null,
            'modification_date'  => null,
            'external_link'      => null,
            'structure_location' => null,
        ];
    }

    protected function getShortDescription(): ?string
    {
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

    protected function getAttachedPhotos(): array
    {
        $attachedPhotos = [];

        if ($this->resource->photos->count()) {
            $attachedPhotos = $this->resource->photos->map(function (Image $photo) {
                return $photo->image;
            })->toArray();
        }

        return $attachedPhotos;
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
