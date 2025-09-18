<?php

namespace MetaFox\Marketplace\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Marketplace\Http\Requests\v1\Listing\Admin\BatchUpdateRequest;
use MetaFox\Marketplace\Http\Requests\v1\Listing\Admin\IndexRequest;
use MetaFox\Marketplace\Http\Resources\v1\Listing\Admin\ListingItemCollection as ItemCollection;
use MetaFox\Marketplace\Policies\ListingPolicy;
use MetaFox\Marketplace\Repositories\ListingAdminRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\SponsorInFeedRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorRequest;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Marketplace\Http\Controllers\Api\ListingAdminController::$controllers;
 */

/**
 * Class ListingAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class ListingAdminController extends ApiController
{
    /**
     * ListingAdminController Constructor
     *
     * @param ListingAdminRepositoryInterface $repository
     */
    public function __construct(protected ListingAdminRepositoryInterface $repository) {}

    /**
     * Browse item
     *
     * @param IndexRequest $request
     * @return mixed
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params  = $request->validated();
        $context = user();
        $limit   = Arr::get($params, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        policy_authorize(ListingPolicy::class, 'viewAny', $context);

        $data = $this->repository->viewMarketplaceListings($context, $params)
            ->paginate($limit, ['marketplace_listings.*']);

        return new ItemCollection($data);
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

        return $this->success([], [], __p($message, ['resource_name' => __p('marketplace::phrase.listing')]));
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

        return $this->success([], [], __p($message, ['resource_name' => __p('marketplace::phrase.listing')]));
    }

    /**
     * @param BatchUpdateRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function batchApprove(BatchUpdateRequest $request): JsonResponse
    {
        $params = $request->validated();
        $ids    = Arr::get($params, 'id', []);

        foreach ($ids as $id) {
            $model = $this->repository->find($id);

            if ($model->isApproved()) {
                continue;
            }

            $this->repository->approve(user(), $id);
        }

        return $this->success([], [], __p('marketplace::phrase.listing_s_has_been_approved'));
    }

    /**
     *
     * @param BatchUpdateRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function batchDelete(BatchUpdateRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $ids     = Arr::get($params, 'id', []);
        $context = user();

        if (!$context->hasPermissionTo('marketplace.moderate')) {
            throw new AuthorizationException();
        }

        foreach ($ids as $id) {
            $this->repository->deleteMarketplaceListing($context, $id);
        }

        return $this->success([], [], __p('marketplace::phrase.listing_s_was_deleted_successfully'));
    }
}
