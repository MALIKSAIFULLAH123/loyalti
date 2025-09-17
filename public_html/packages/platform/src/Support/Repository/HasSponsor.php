<?php

namespace MetaFox\Platform\Support\Repository;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Exceptions\PrivacyException;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\SEO\ActionMeta;
use MetaFox\SEO\PayloadActionMeta;

/**
 * Trait HasSponsor.
 */
trait HasSponsor
{
    public function unsponsor(User $context, int $id): bool
    {
        $resource = $this->find($id);

        if (!$resource instanceof Content) {
            return false;
        }

        gate_authorize($context, 'unsponsor', $resource, 0);

        app('events')->dispatch('advertise.sponsorship.unsponsor', [$context, $resource], true);

        return true;
    }

    /**
     * @param User $context
     * @param int  $id
     * @param int  $sponsor
     *
     * @return bool
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function sponsor(User $context, int $id, int $sponsor): bool
    {
        /*
         * TODO: remove it after implement endpoint for unsponsor item
         */
        if ($sponsor == 0) {
            return $this->unsponsor($context, $id);
        }

        $resource = $this->find($id);

        if (!$resource instanceof Content) {
            return false;
        }

        if ($resource instanceof HasPrivacy && $resource->privacy == MetaFoxPrivacy::ONLY_ME) {
            throw new PrivacyException(403, __p('core::phrase.the_current_item_privacy_is_set_to_only_me'));
        }

        gate_authorize($context, 'sponsor', $resource, 1);

        app('events')->dispatch('advertise.sponsorship.sponsor_free', [$context, $resource], true);

        return true;
    }

    /**
     * @param Content $content
     *
     * @return void
     */
    public function enableSponsor(Content $content): void
    {
        $content->update(['is_sponsor' => 1]);
    }

    /**
     * @param Content $content
     *
     * @return void
     */
    public function disableSponsor(Content $content): void
    {
        $content->update(['is_sponsor' => 0]);
    }

    /**
     * @param Content $model
     *
     * @return bool
     * @deprecated
     */
    public function isSponsor(Content $model): bool
    {
        return $model->isSponsored();
    }

    public function getRandomSponsoredItems(User $context, int $limit, ?array $loadedItemIds = null): Collection
    {
        if ($limit <= 0) {
            return collect([]);
        }

        $model = $this->getModel()->newModelInstance();

        $sponsoredItemIds = app('events')->dispatch('advertise.sponsorship.get_sponsored_item_ids_by_type', [$context, $model->entityType(), $limit, $loadedItemIds, true], true);

        $hasSponsored = is_array($sponsoredItemIds);

        if ($hasSponsored && !count($sponsoredItemIds)) {
            return collect([]);
        }

        $query = $this->getModel()->newQuery();

        match ($hasSponsored) {
            true    => $query->whereIn($model->getKeyName(), $sponsoredItemIds),
            default => $query->where(['is_sponsor' => 1]),
        };

        return $query
            ->limit($limit)
            ->inRandomOrder()
            ->get();
    }

    public function getSponsoredItem(array $notInIds, ?int $sponsorStart = null, array $with = []): ?Content
    {
        $query = $this->getModel()->newModelQuery()
            ->with($with)
            ->where('is_sponsor', '=', 1)
            ->where('id', '<', $sponsorStart ?? 0)
            ->inRandomOrder();

        if (count($notInIds)) {
            $query->whereNotIn('id', $notInIds);
        }

        return $query->first();
    }

    public function askingForPurchasingSponsorship(User $context, Content $content): array
    {
        $askingForSponsorship = app('events')->dispatch('advertise.ask_for_sponsorship', [$context, $content], true);

        if (true !== $askingForSponsorship) {
            return [];
        }

        $actionMeta   = new ActionMeta();

        $resourceName = __p_type_key($content->entityType());

        $actionMeta->continueAction()
            ->type('advertise/ask_for_purchasing_sponsorship')
            ->payload(
                PayloadActionMeta::payload()
                    ->setAttribute('confirm', [
                        'title'   => __p('core::phrase.confirm'),
                        'message' => __p('core::phrase.would_you_like_to_purchase_sponsorship_for_this_item', ['item_name' => $resourceName]),
                    ])
                    ->setAttribute('action', 'advertise/purchaseSponsorItem')
            );

        return $actionMeta->toArray();
    }
}
