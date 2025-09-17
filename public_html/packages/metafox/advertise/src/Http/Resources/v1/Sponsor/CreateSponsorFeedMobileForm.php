<?php

namespace MetaFox\Advertise\Http\Resources\v1\Sponsor;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use MetaFox\Advertise\Policies\SponsorPolicy;
use MetaFox\Advertise\Repositories\SponsorRepositoryInterface;
use MetaFox\Advertise\Services\Contracts\SponsorSettingServiceInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Advertise\Models\Sponsor as Model;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Exceptions\PrivacyException;
use MetaFox\Platform\MetaFoxPrivacy;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateSponsorFeedMobileForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateSponsorFeedMobileForm extends CreateSponsorMobileForm
{
    /**
     * @var Content|null
     */
    protected ?Content $item = null;

    protected function prepare(): void
    {
        $sponsorData = $this->item->activity_feed?->toSponsorData() ?: [];

        $title = Arr::get($sponsorData, 'title');

        if (is_string($title) && Str::length($title) > 255) {
            $title = Str::substr($title, 0, 255);
        }

        $this->title(__p('advertise::phrase.sponsor_feed'))
            ->action('advertise/sponsor/feed')
            ->asPost()
            ->setValue([
                'title'            => $title,
                'item_type'        => $this->item->entityType(),
                'item_id'          => $this->item->entityId(),
                'total_impression' => 1000,
                'start_date'       => Carbon::now()->toISOString(),
                'end_date'         => null,
                'age_from'         => '',
            ]);
    }

    /**
     * @param  int|null                $id
     * @param  string|null             $itemType
     * @param  int|null                $itemId
     * @return void
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function boot(?int $id = null, ?string $itemType = null, ?int $itemId = null): void
    {
        $this->item = resolve(SponsorRepositoryInterface::class)->getMorphedItem($itemType, $itemId);

        $context = user();

        if (null === $this->item) {
            abort(403, __p('advertise::validation.this_item_is_not_available_for_feed_sponsor'));
        }

        if ($this->item instanceof HasPrivacy && $this->item->privacy == MetaFoxPrivacy::ONLY_ME) {
            throw new PrivacyException(403, __p('core::phrase.the_current_item_privacy_is_set_to_only_me'));
        }

        policy_authorize(SponsorPolicy::class, 'purchaseSponsorInFeed', $context, $this->item);

        $this->isFree = $this->getInitialPrice() == 0;
    }

    protected function getInitialPrice(): ?float
    {
        return resolve(SponsorSettingServiceInterface::class)->getPriceForPayment(user(), $this->item->activity_feed);
    }
}
