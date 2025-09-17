<?php

namespace MetaFox\Marketplace\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Marketplace\Http\Requests\v1\Invoice\SearchFormRequest;
use MetaFox\Marketplace\Http\Requests\v1\Listing\IndexRequest;
use MetaFox\Marketplace\Http\Requests\v1\Listing\StoreRequest;
use MetaFox\Marketplace\Http\Requests\v1\Listing\UpdateRequest;
use MetaFox\Marketplace\Http\Resources\v1\Listing\ListingDetail as Detail;
use MetaFox\Marketplace\Http\Resources\v1\Listing\ListingItemCollection;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Marketplace\Policies\ListingPolicy;
use MetaFox\Marketplace\Repositories\InviteRepositoryInterface;
use MetaFox\Marketplace\Repositories\ListingHistoryRepositoryInterface;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Platform\Exceptions\PermissionDeniedException;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\FeatureRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorInFeedRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorRequest;
use MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\User\Support\Facades\UserEntity;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class ListingController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @ignore
 * @codeCoverageIgnore
 * @group marketplace
 * @authenticated
 */
class ListingController extends ApiController
{
    /**
     * @var ListingRepositoryInterface
     */
    private ListingRepositoryInterface $repository;

    /**
     * @param ListingRepositoryInterface $repository
     */
    public function __construct(ListingRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse listing.
     *
     * @param IndexRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException|AuthorizationException
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $owner = $context;

        $view = Arr::get($params, 'view');

        $limit = Arr::get($params, 'limit');

        if ($params['user_id'] > 0) {
            $owner = UserEntity::getById($params['user_id'])->detail;

            policy_authorize(ListingPolicy::class, 'viewOnProfilePage', $context, $owner);
        }

        policy_authorize(ListingPolicy::class, 'viewAny', $context, $owner);

        $data = match ($view) {
            Browse::VIEW_SPONSOR => $this->repository->getRandomSponsoredItems($context, $limit ?? 4),
            default              => $this->repository->viewMarketplaceListings($context, $owner, $params),
        };

        return $this->success($this->resolveItemCollection($data));
    }

    /**
     * View listing.
     *
     * @param int $id
     *
     * @return Detail
     * @throws AuthorizationException|AuthenticationException
     */
    public function show(int $id): Detail
    {
        $context = user();

        $data = $this->repository->viewMarketplaceListing($context, $id);

        /*
         * Mark as visited after accessing to detail page
         */
        resolve(InviteRepositoryInterface::class)->visitedAt($context, $data);

        /*
         * Mark as history
         */
        resolve(ListingHistoryRepositoryInterface::class)->createHistory(
            $context->entityId(),
            $context->entityType(),
            $id
        );

        return ResourceGate::asDetail($data, null);
    }

    /**
     * Create listing.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidatorException
     * @throws PermissionDeniedException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $context = $owner = user();

        $params = $request->validated();

        app('flood')->checkFloodControlWhenCreateItem($context, Listing::ENTITY_TYPE);

        app('quota')->checkQuotaControlWhenCreateItem($context, Listing::ENTITY_TYPE);

        if ($params['owner_id'] > 0) {
            if ($context->entityId() != $params['owner_id']) {
                $owner = UserEntity::getById($params['owner_id'])->detail;
            }
        }

        $marketplace = $this->repository->createMarketplaceListing($context, $owner, $params);

        $message = __p('marketplace::phrase.listing_successfully_created');

        if (!$marketplace->is_approved) {
            $message = __p('core::phrase.thanks_for_your_item_for_approval');
        }

        $ownerPendingMessage = $marketplace->getOwnerPendingMessage();

        if (null !== $ownerPendingMessage) {
            $message = $ownerPendingMessage;
        }
        $meta = $this->repository->askingForPurchasingSponsorship($context, $marketplace);

        return $this->success($this->resolveDetailResource($marketplace), $meta, $message);
    }

    /**
     * Update listing.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return Detail
     * @throws AuthenticationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $marketplace = $this->repository->updateMarketplaceListing(user(), $id, $params);

        return $this->success(
            $this->resolveDetailResource($marketplace),
            [],
            __p('marketplace::phrase.listing_has_been_updated_successfully')
        );
    }

    /**
     * Remove listing.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteMarketplaceListing(user(), $id);

        return $this->success([], [], __p('marketplace::phrase.successfully_deleted_listing'));
    }

    /**
     * Feature listing.
     *
     * @param FeatureRequest $request
     * @param int            $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function feature(FeatureRequest $request, int $id): JsonResponse
    {
        $params  = $request->validated();
        $feature = (int) $params['feature'];
        $context = user();

        match ($feature) {
            1       => $this->repository->featureFree($context, $id),
            default => $this->repository->unfeature($context, $id),
        };

        $message = match ($feature) {
            1       => __p('marketplace::phrase.listing_featured_successfully'),
            default => __p('marketplace::phrase.listing_unfeatured_successfully'),
        };

        $listing = $this->repository->find($id);

        return $this->success(new Detail($listing), [], $message);
    }

    /**
     * Sponsor listing.
     *
     * @param SponsorRequest $request
     * @param int            $id
     *
     * @return JsonResponse
     * @throws AuthenticationException|AuthorizationException
     */
    public function sponsor(SponsorRequest $request, int $id): JsonResponse
    {
        $params  = $request->validated();
        $sponsor = $params['sponsor'];

        $this->repository->sponsor(user(), $id, $sponsor);

        $listing = $this->repository->find($id);

        $isSponsor        = (bool) $sponsor;
        $isPendingSponsor = $isSponsor && !$listing->is_sponsor;

        $message = $isPendingSponsor ? 'core::phrase.resource_sponsored_successfully_please_waiting_for_approval' : ($isSponsor ? 'core::phrase.resource_sponsored_successfully' : 'core::phrase.resource_unsponsored_successfully');
        $message = __p($message, ['resource_name' => __p('marketplace::phrase.listing')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success($this->resolveDetailResource($listing), [], $message);
    }

    /**
     * Approve listing.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function approve(int $id): JsonResponse
    {
        $context = user();

        $listing = $this->repository->approve($context, $id);

        $resource = ResourceGate::asDetail($listing);

        return $this->success($resource, [], __p('marketplace::phrase.listing_has_been_approved_successfully'));
    }

    /**
     * Reopen listing.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function reopen(int $id): JsonResponse
    {
        $context = user();

        $this->repository->reopenListing($context, $id);

        $listing = $this->repository->find($id);

        $resource = ResourceGate::asDetail($listing);

        $data = $resource->toArray(request());

        return $this->success([
            'id'         => $id,
            'is_expired' => false,
            'extra'      => Arr::get($data, 'extra', []),
        ], [], __p('marketplace::phrase.listing_successfully_reopened'));
    }

    /**
     * Sponsor in feed.
     *
     * @param SponsorInFeedRequest $request
     * @param int                  $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function sponsorInFeed(SponsorInFeedRequest $request, int $id): JsonResponse
    {
        $params  = $request->validated();
        $sponsor = $params['sponsor'];

        $this->repository->sponsorInFeed(user(), $id, $sponsor);

        $listing = $this->repository->find($id);

        $isSponsor        = (bool) $sponsor;
        $isPendingSponsor = $isSponsor && !$listing->sponsor_in_feed;

        $message = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_in_feed_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_in_feed_successfully'
                : 'core::phrase.resource_unsponsored_in_feed_successfully');
        $message = __p($message, ['resource_name' => __p('marketplace::phrase.listing')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success($this->resolveDetailResource($listing), [], $message);
    }

    public function searchSuggestion(SearchFormRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = $owner = user();

        $data = $this->repository->getListingForForm($context, $params);

        return $this->success($data);
    }

    protected function resolveDetailResource(Listing $listing): Detail
    {
        /**
         * @var Detail $resource
         */
        $resource = ResourceGate::asDetail($listing, null);

        if ($resource instanceof JsonResource) {
            return $resource;
        }

        return new Detail($listing);
    }

    protected function resolveItemCollection(Collection|Paginator $collection): ResourceCollection
    {
        return resolve(ListingItemCollection::class, [
            'resource' => $collection,
        ]);
    }
}
