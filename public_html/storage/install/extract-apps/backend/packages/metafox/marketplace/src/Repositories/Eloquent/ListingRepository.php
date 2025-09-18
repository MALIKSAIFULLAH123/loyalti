<?php

namespace MetaFox\Marketplace\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use MetaFox\Core\Repositories\AttachmentRepositoryInterface;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Marketplace\Jobs\DeleteListingJob;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Marketplace\Models\ListingPrice;
use MetaFox\Marketplace\Notifications\ExpiredNotification;
use MetaFox\Marketplace\Policies\CategoryPolicy;
use MetaFox\Marketplace\Policies\ListingPolicy;
use MetaFox\Marketplace\Repositories\CategoryRepositoryInterface;
use MetaFox\Marketplace\Repositories\ImageRepositoryInterface;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Marketplace\Support\Browse\Scopes\Invoice\ViewScope as InvoiceViewScope;
use MetaFox\Marketplace\Support\Browse\Scopes\Listing\FilterPriceScope;
use MetaFox\Marketplace\Support\Browse\Scopes\Listing\SortScope;
use MetaFox\Marketplace\Support\Browse\Scopes\Listing\ViewScope;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Exceptions\PrivacyException;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BoundsScope;
use MetaFox\Platform\Support\Browse\Scopes\CategoryScope;
use MetaFox\Platform\Support\Browse\Scopes\PrivacyScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\TagScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Class ListingRepository.
 * @property Listing $model
 * @method   Listing getModel()
 * @method   Listing find($id, $columns = ['*'])()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 */
class ListingRepository extends AbstractRepository implements ListingRepositoryInterface
{
    use HasSponsor;
    use HasFeatured;
    use HasSponsorInFeed;
    use HasApprove;
    use UserMorphTrait;
    use CollectTotalItemStatTrait;

    public function model(): string
    {
        return Listing::class;
    }

    protected function categoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }

    protected const TIMESTAMP = 0;

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewMarketplaceListings(User $context, User $owner, array $attributes): Paginator
    {
        $view = $attributes['view'];

        $limit = $attributes['limit'];

        $profileId = Arr::get($attributes, 'user_id', 0);

        $this->withUserMorphTypeActiveScope();

        if ($view == Browse::VIEW_FEATURE) {
            return $this->findFeature($limit);
        }

        if (!$this->hasPendingView($context, $view, $profileId)) {
            throw new AuthorizationException(__p('core::validation.this_action_is_unauthorized'), 403);
        }

        if ($view === ViewScope::VIEW_EXPIRE) {
            policy_authorize(ListingPolicy::class, 'viewExpire', $context, $owner, $profileId);
        }

        $categoryId = Arr::get($attributes, 'category_id', 0);

        if ($categoryId > 0) {
            $category = $this->categoryRepository()->find($categoryId);

            policy_authorize(CategoryPolicy::class, 'viewActive', $context, $category);
        }

        if ($profileId > 0 && $profileId == $context->entityId()) {
            if (!in_array($view, [Browse::VIEW_PENDING, ViewScope::VIEW_EXPIRE, ViewScope::VIEW_PROFILE])) {
                $attributes['view'] = Browse::VIEW_MY;
            }
        }

        $query = $this->buildQueryViewListings($context, $owner, $attributes);

        $relation = ['marketplaceText', 'photos', 'tagData'];

        return $query
            ->with($relation)
            ->simplePaginate($limit, ['marketplace_listings.*']);
    }

    protected function hasPendingView(User $context, string $view, int $profileId): bool
    {
        if ($view !== Browse::VIEW_PENDING) {
            return true;
        }

        if ($profileId == $context->entityId()) {
            return true;
        }

        if ($context->isGuest()) {
            return false;
        }

        if (!$context->hasPermissionTo('marketplace.approve')) {
            return false;
        }

        return true;
    }

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Listing
     * @throws AuthorizationException
     */
    public function viewMarketplaceListing(User $context, int $id): Listing
    {
        $listing = $this
            ->withUserMorphTypeActiveScope()
            ->with(['marketplaceText', 'categories', 'attachments', 'ownerEntity', 'userEntity'])
            ->find($id);

        policy_authorize(ListingPolicy::class, 'view', $context, $listing);

        $listing->with(['marketplaceText', 'categories', 'attachments', 'ownerEntity', 'userEntity']);

        $listing->incrementTotalView();

        $listing->refresh();

        return $listing;
    }

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Listing
     * @throws AuthorizationException
     */
    public function createMarketplaceListing(User $context, User $owner, array $attributes): Listing
    {
        policy_authorize(ListingPolicy::class, 'create', $context, $owner);

        $price = Arr::get($attributes, 'price');

        if (is_array($price)) {
            Arr::set($attributes, 'price', json_encode($price));
        }

        $timestamps = $this->getTimestamp();

        $attributes = array_merge($attributes, [
            'user_id'          => $context->entityId(),
            'user_type'        => $context->entityType(),
            'owner_id'         => $owner->entityId(),
            'owner_type'       => $owner->entityType(),
            'is_approved'      => (int) policy_check(ListingPolicy::class, 'autoApprove', $context, $owner),
            'start_expired_at' => $timestamps['expired_at'],
            'notify_at'        => $timestamps['notify_at'],
        ]);

        $attributes['title'] = $this->cleanTitle($attributes['title']);

        if (null !== Arr::get($attributes, 'short_description')) {
            $attributes['short_description'] = $this->cleanTitle($attributes['short_description']);
        }

        $marketplace = new Listing();

        $marketplace->fill($attributes);

        if ($attributes['privacy'] == MetaFoxPrivacy::CUSTOM) {
            $marketplace->setPrivacyListAttribute($attributes['list']);
        }

        $marketplace->save();

        $this->handleAttachments($marketplace, Arr::get($attributes, 'attachments'));

        $this->handleAttachedPhotos($context, $marketplace, Arr::get($attributes, 'attached_photos'), false);

        $marketplace->refresh();

        $this->addPrices($marketplace);

        return $marketplace;
    }

    protected function handleAttachedPhotos(
        User $context,
        Listing $marketplace,
        ?array $attachedPhotos,
        bool $isUpdated = true
    ): void {
        resolve(ImageRepositoryInterface::class)->updateImages(
            $context,
            $marketplace->entityId(),
            $attachedPhotos,
            $isUpdated
        );
    }

    protected function handleAttachments(Listing $marketplace, ?array $attachments): void
    {
        if (null === $attachments) {
            return;
        }

        resolve(AttachmentRepositoryInterface::class)->updateItemId($attachments, $marketplace);
    }

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Listing
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function updateMarketplaceListing(User $context, int $id, array $attributes): Listing
    {
        $listing = $this->withUserMorphTypeActiveScope()->find($id);

        policy_authorize(ListingPolicy::class, 'update', $context, $listing);

        if (isset($attributes['privacy']) && !$context->can('updatePrivacy', [$listing, $attributes['privacy']])) {
            throw new PrivacyException(403, __p('core::phrase.the_current_item_is_either_sponsored_or_featured'));
        }

        if (null !== Arr::get($attributes, 'title')) {
            $attributes['title'] = $this->cleanTitle($attributes['title']);
        }

        if (null !== Arr::get($attributes, 'short_description')) {
            $attributes['short_description'] = $this->cleanTitle($attributes['short_description']);
        }

        if (!$listing->is_approved) {
            // Disallow marking as Sold when item is pending
            if (Arr::has($attributes, 'is_sold')) {
                unset($attributes['is_sold']);
            }
        }

        $listing->fill($attributes);

        if (Arr::get($attributes, 'privacy') == MetaFoxPrivacy::CUSTOM) {
            $listing->setPrivacyListAttribute($attributes['list']);
        }

        $listing->save();

        $this->handleAttachments($listing, Arr::get($attributes, 'attachments'));

        $this->handleAttachedPhotos($context, $listing, Arr::get($attributes, 'attached_photos'));

        $listing->refresh();

        $this->addPrices($listing);

        $this->updateFeedStatus($listing);

        return $listing;
    }

    protected function updateFeedStatus(Listing $listing): void
    {
        app('events')->dispatch('activity.feed.mark_as_pending', [$listing]);
    }

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteMarketplaceListing(User $context, int $id): bool
    {
        $listing = $this->withUserMorphTypeActiveScope()->find($id);

        policy_authorize(ListingPolicy::class, 'delete', $context, $listing);

        if (!$this->delete($id)) {
            return false;
        }

        DeleteListingJob::dispatch($id);

        return true;
    }

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     */
    private function buildQueryViewListings(User $context, User $owner, array $attributes): Builder
    {
        $sort       = Arr::get($attributes, 'sort');
        $sortType   = Arr::get($attributes, 'sort_type');
        $when       = Arr::get($attributes, 'when');
        $view       = Arr::get($attributes, 'view');
        $search     = Arr::get($attributes, 'q');
        $searchTag  = Arr::get($attributes, 'tag', MetaFoxConstant::EMPTY_STRING);
        $categoryId = Arr::get($attributes, 'category_id');
        $profileId  = Arr::get($attributes, 'user_id', 0);
        $countryIso = Arr::get($attributes, 'country_iso');
        $priceFrom  = Arr::get($attributes, 'price_from');
        $priceTo    = Arr::get($attributes, 'price_to');
        $bounds     = [
            'west'  => Arr::get($attributes, 'bounds_west'),
            'east'  => Arr::get($attributes, 'bounds_east'),
            'south' => Arr::get($attributes, 'bounds_south'),
            'north' => Arr::get($attributes, 'bounds_north'),
        ];
        $currencyId = app('currency')->getUserCurrencyId($context);
        $isFeatured = Arr::get($attributes, 'is_featured', false);

        // Scopes.
        $privacyScope = new PrivacyScope();
        $privacyScope->setUserId($context->entityId());
        $privacyScope->setModerationPermissionName('marketplace.moderate');
        $privacyScope->setHasUserBlock(true);

        $sortScope = new SortScope();
        $sortScope->setSort($sort)
            ->setSortType($sortType);

        $whenScope = new WhenScope();
        $whenScope->setWhen($when);

        $viewScope = new ViewScope();
        $viewScope->setUserContext($context)
            ->setView($view)
            ->setProfileId($profileId);

        /**
         * @var FilterPriceScope $priceScope
         */
        $priceScope = resolve(FilterPriceScope::class, [
            'priceFrom' => $priceFrom,
            'priceTo'   => $priceTo,
        ]);

        $boundsScope = new BoundsScope();
        $boundsScope->setBounds($bounds);

        $query = $this->getModel()
            ->newQuery();

        $hasJoinPriceTable = false;

        if (in_array($sort, [SortScope::SORT_HIGHEST_PRICE, SortScope::SORT_LOWEST_PRICE])) {
            $hasJoinPriceTable = true;
        }

        if (is_numeric($priceFrom) || is_numeric($priceTo)) {
            $hasJoinPriceTable = true;
        }

        if ($hasJoinPriceTable) {
            $query->leftJoin('marketplace_listing_prices', function (JoinClause $joinClause) use ($currencyId) {
                $joinClause->on('marketplace_listing_prices.listing_id', '=', 'marketplace_listings.id')
                    ->where('marketplace_listing_prices.currency_id', $currencyId);
            });
        }

        if (MetaFoxConstant::EMPTY_STRING !== $search) {
            $query = $query->addScope(new SearchScope($search, ['title']));
        }

        if (MetaFoxConstant::EMPTY_STRING !== $searchTag) {
            $tagScope = new TagScope($searchTag);

            $query = $query->addScope($tagScope);
        }

        if ($owner->entityId() != $context->entityId()) {
            $privacyScope->setOwnerId($owner->entityId());

            $viewScope->setIsViewOwner(true);

            if (!policy_check(ListingPolicy::class, 'viewExpire', $context, $owner, $profileId)) {
                $viewScope->setView(ViewScope::VIEW_ALIVE);
            }

            if (!$context->hasPermissionTo('marketplace.approve')) {
                $query->where('marketplace_listings.is_approved', '=', 1);
            }
        }

        if ($categoryId > 0) {
            $categoryScope = new CategoryScope();

            $categoryScope->setCategories($this->categoryRepository()->getChildrenIds($categoryId));

            $query = $query->addScope($categoryScope);
        }

        $this->applyDisplaySetting($query, $owner, $view);

        if (null !== $countryIso) {
            $query->where('marketplace_listings.country_iso', '=', $countryIso);
        }

        if ($isFeatured) {
            $query->where('marketplace_listings.is_featured', '=', 1);
        }

        return $query
            ->addScope($privacyScope)
            ->addScope($whenScope)
            ->addScope($boundsScope)
            ->addScope($viewScope)
            ->addScope($boundsScope)
            ->addScope($sortScope)
            ->addScope($priceScope);
    }

    /**
     * @param  Builder $query
     * @param  User    $owner
     * @param  string  $view
     * @return void
     */
    private function applyDisplaySetting(Builder $query, User $owner, string $view): void
    {
        if ($view == Browse::VIEW_MY) {
            return;
        }

        if ($owner instanceof HasPrivacyMember) {
            return;
        }

        $query->where('marketplace_listings.owner_type', '=', $owner->entityType());
    }

    public function findFeature(int $limit = 4): Paginator
    {
        return $this->getModel()->newQuery()
            ->where('is_featured', Listing::IS_FEATURED)
            ->where('is_approved', '=', 1)
            ->where('is_sold', 0)
            ->where(function (Builder $builder) {
                $builder->where('marketplace_listings.start_expired_at', '>', Carbon::now()->timestamp)
                    ->orWhere('marketplace_listings.start_expired_at', '=', 0);
            })
            ->orderByDesc(HasFeature::FEATURED_AT_COLUMN)
            ->simplePaginate($limit);
    }

    public function findSponsor(int $limit = 4): Paginator
    {
        return $this->getModel()->newQuery()
            ->where('is_sponsor', Listing::IS_SPONSOR)
            ->where('is_approved', '=', 1)
            ->simplePaginate($limit);
    }

    public function forceDeleteListing(int $id): void
    {
        $listing = Listing::onlyTrashed()
            ->where('id', '=', $id)
            ->first();

        if (null === $listing) {
            return;
        }

        if ($listing->paidInvoices()->count()) {
            $this->deleteUnusedListingData($listing);

            return;
        }

        $listing->forceDelete();
    }

    public function deleteUnusedListingData(Listing $listing): void
    {
        $listing->marketplaceText()->delete();

        $listing->invites()->each(function ($data) {
            $data->delete();
        });

        $listing->categories()->sync([]);

        $listing->photos()->each(function ($data) {
            $data->delete();
        });

        $listing->attachments()->each(function ($data) {
            $data->delete();
        });

        $listing->histories()->delete();

        $listing->pendingInvoices()->delete();
    }

    public function closeListingAfterPayment(int $id): bool
    {
        $listing = $this->withUserMorphTypeActiveScope()->find($id);

        if (!$listing->auto_sold) {
            return false;
        }

        $listing->fill([
            'is_sold' => true,
        ]);

        $listing->save();

        return true;
    }

    protected function getTimestamp(): array
    {
        $days       = (int) Settings::get('marketplace.days_to_expire', 30);
        $notifyDays = (int) Settings::get('marketplace.days_to_notify_before_expire', 0);

        if ($days == 0) {
            return [
                'expired_at' => 0,
                'notify_at'  => 0,
            ];
        }

        $timestamp       = Carbon::now()->addDays($days)->timestamp;
        $notifyTimestamp = $notifyDays > 0 ? Carbon::parse($timestamp)->subDays($notifyDays)->timestamp : 0;

        return [
            'expired_at' => $timestamp,
            'notify_at'  => $notifyTimestamp,
        ];
    }

    public function reopenListing(User $context, int $id): bool
    {
        $listing = $this->withUserMorphTypeActiveScope()->find($id);

        policy_authorize(ListingPolicy::class, 'reopen', $context, $listing);

        $timestamps = $this->getTimestamp();
        $attributes = [
            'start_expired_at' => $timestamps['expired_at'],
            'is_notified'      => false,
            'is_sold'          => false,
            'notify_at'        => $timestamps['notify_at'],
        ];

        $listing->fill($attributes);

        $listing->save();

        return true;
    }

    protected function getNotifiedListings(): Enumerable
    {
        $timestamp = Carbon::now()->timestamp;

        return $this->getModel()->newModelQuery()
            ->where([
                'is_notified' => 0,
            ])
            ->where('start_expired_at', '>', $timestamp)
            ->where('notify_at', '<', $timestamp)
            ->where('notify_at', '>', 0)
            ->get();
    }

    public function sendExpiredNotifications(): void
    {
        $notifiedListings = $this->getNotifiedListings();

        if (!$notifiedListings->count()) {
            return;
        }

        $this->processNotifiedListings($notifiedListings);
    }

    protected function processNotifiedListings(Enumerable $notifiedListings)
    {
        $successListingIds = [];

        foreach ($notifiedListings as $notifiedListing) {
            if (null === $notifiedListing->user) {
                continue;
            }

            $this->toExpiredNotification($notifiedListing);

            $successListingIds[] = $notifiedListing->entityId();
        }

        if (!count($successListingIds)) {
            return;
        }

        $successListingIds = array_unique($successListingIds);

        $this->getModel()->newModelQuery()
            ->whereIn('id', $successListingIds)
            ->update([
                'is_notified' => 1,
            ]);
    }

    protected function toExpiredNotification(Listing $listing): void
    {
        $notification = new ExpiredNotification($listing);

        $days = Carbon::parse($listing->start_expired_at)->floatDiffInDays(Carbon::now());

        $notification->setExpiredDays($days);

        $params = [$listing->user, $notification];

        Notification::send(...$params);
    }

    public function migratePrices(): void
    {
        $listings = $this->getModel()->newQuery()
            ->get();

        if (!$listings->count()) {
            return;
        }

        foreach ($listings as $listing) {
            $this->addPrices($listing);
        }
    }

    public function addPrices(Listing $listing): void
    {
        $prices = $listing->price;

        if (!count($prices)) {
            return;
        }

        $upserts = $deleteCurrencyIds = [];

        foreach ($prices as $currencyId => $price) {
            if (!is_numeric($price)) {
                $deleteCurrencyIds[] = $currencyId;
                continue;
            }

            $upserts[] = [
                'listing_id'  => $listing->entityId(),
                'currency_id' => $currencyId,
                'price'       => $price,
            ];
        }

        $this->deleteListingPrices($listing->entityId(), $deleteCurrencyIds);

        ListingPrice::query()->upsert($upserts, ['listing_id', 'currency_id'], ['price']);
    }

    protected function deleteListingPrices(int $listingId, array $currencyIds): void
    {
        if (empty($currencyIds)) {
            return;
        }

        ListingPrice::query()
            ->where('listing_id', $listingId)
            ->whereIn('currency_id', $currencyIds)
            ->delete();
    }

    public function getListingForForm(User $context, array $attributes = []): array
    {
        $context   = $owner = user();
        $view      = Arr::get($attributes, 'view', InvoiceViewScope::VIEW_DEFAULT);
        $search    = Arr::get($attributes, 'q');
        $listingId = Arr::get($attributes, 'listing_id');
        $limit     = Arr::get($attributes, 'limit', 10);

        $query = $this->getModel()
            ->newQuery();

        if ($view == InvoiceViewScope::VIEW_SOLD) {
            $query->where([
                'marketplace_listings.user_id'   => $context->entityId(),
                'marketplace_listings.user_type' => $context->entityType(),
            ])->has('invoices');
        }

        if ($view == InvoiceViewScope::VIEW_BOUGHT) {
            $query->whereHas('invoices', function (Builder $q) use ($context) {
                $q->where([
                    'marketplace_invoices.user_id'   => $context->entityId(),
                    'marketplace_invoices.user_type' => $context->entityType(),
                ]);
            });
        }

        if ($listingId) {
            $query->orderBy(DB::raw("CASE WHEN marketplace_listings.id = {$listingId} THEN 1 ELSE 2 END"));
        }

        if (!$listingId && $search) {
            $query->addScope(new SearchScope($search, ['title']));
        }

        return $query->limit($limit)->get()->map(function (Listing $listing) {
            return [
                'label'         => $listing->toTitle(),
                'value'         => $listing->entityId(),
                'id'            => $listing->entityId(),
                'module_name'   => $listing->moduleName(),
                'resource_name' => $listing->entityType() . '_option',
            ];
        })->toArray();
    }
}
