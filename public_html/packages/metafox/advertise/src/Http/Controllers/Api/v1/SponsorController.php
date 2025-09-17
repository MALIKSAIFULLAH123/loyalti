<?php

namespace MetaFox\Advertise\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Advertise\Http\Requests\v1\Sponsor\IndexRequest;
use MetaFox\Advertise\Http\Requests\v1\Sponsor\StoreFeedRequest;
use MetaFox\Advertise\Http\Requests\v1\Sponsor\StoreRequest;
use MetaFox\Advertise\Http\Requests\v1\Sponsor\UpdateRequest;
use MetaFox\Advertise\Http\Requests\v1\Sponsor\UpdateTotalRequest;
use MetaFox\Advertise\Http\Resources\v1\Sponsor\SponsorDetail as Detail;
use MetaFox\Advertise\Http\Resources\v1\Sponsor\SponsorItemCollection as ItemCollection;
use MetaFox\Advertise\Policies\SponsorPolicy;
use MetaFox\Advertise\Repositories\SponsorRepositoryInterface;
use MetaFox\Advertise\Support\Support;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\Eloquent\DriverRepository;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Advertise\Http\Controllers\Api\SponsorController::$controllers;
 */

/**
 * Class SponsorController.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class SponsorController extends ApiController
{
    /**
     * @var SponsorRepositoryInterface
     */
    private SponsorRepositoryInterface $repository;

    /**
     * SponsorController Constructor.
     *
     * @param SponsorRepositoryInterface $repository
     */
    public function __construct(SponsorRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     *
     * @return ItemCollection
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $context = user();

        policy_authorize(SponsorPolicy::class, 'viewAny', $context);

        $data = $this->repository->viewSponsors($context, $params);

        return new ItemCollection($data);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $item = $this->repository->getMorphedItem(Arr::get($params, 'item_type'), Arr::get($params, 'item_id'));

        $context = user();

        policy_authorize(SponsorPolicy::class, 'purchaseSponsor', $context, $item);

        $sponsor = $this->repository->createSponsor($context, $item, $params);

        $message = __p('advertise::phrase.sponsor_successfully_created');

        if ($sponsor->status == Support::ADVERTISE_STATUS_UNPAID) {
            $message = __p('advertise::phrase.your_sponsor_has_successfully_been_submitted');
        }

        return $this->success([
            'id' => $sponsor->entityId(),
        ], [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url' => 'advertise/sponsor',
                ],
            ],
        ], $message);
    }

    /**
     * Store item.
     *
     * @param StoreFeedRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function storeFeed(StoreFeedRequest $request): JsonResponse
    {
        $params = $request->validated();

        $item = $this->repository->getMorphedItem(Arr::get($params, 'item_type'), Arr::get($params, 'item_id'));

        Arr::forget($params, ['item_type', 'item_id']);

        $context = user();

        policy_authorize(SponsorPolicy::class, 'purchaseSponsorInFeed', $context, $item);

        $sponsor = $this->repository->createFeedSponsor($context, $item, $params);

        $message = __p('advertise::phrase.sponsor_successfully_created');

        if ($sponsor->status == Support::ADVERTISE_STATUS_UNPAID) {
            $message = __p('advertise::phrase.your_sponsor_has_successfully_been_submitted');
        }

        return $this->success([
            'id' => $sponsor->entityId(),
        ], [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url' => 'advertise/sponsor',
                ],
            ],
        ], $message);
    }

    /**
     * View item.
     *
     * @param int $id
     *
     * @return Detail
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function show($id): Detail
    {
        $context = user();

        $sponsor = $this->repository->find($id);

        policy_authorize(SponsorPolicy::class, 'view', $context, $sponsor);

        return new Detail($sponsor);
    }

    /**
     * Update item.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $sponsor = $this->repository->find($id);

        $context = user();

        policy_authorize(SponsorPolicy::class, 'update', $context, $sponsor);

        $this->repository->updateSponsor($context, $sponsor, $params);

        return $this->success(new Detail($sponsor), [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url' => '/advertise/sponsor',
                ],
            ],
        ], __p('advertise::phrase.sponsor_successfully_updated'));
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function destroy(int $id): JsonResponse
    {
        $sponsor = $this->repository->find($id);

        $context = user();

        policy_authorize(SponsorPolicy::class, 'delete', $context, $sponsor);

        $this->repository->deleteSponsor($sponsor);

        return $this->success([
            'id' => $sponsor->entityId(),
        ], [], __p('advertise::phrase.sponsor_successfully_deleted'));
    }

    public function getSponsorForm(Request $request, string $itemType, int $itemId): JsonResponse
    {
        $resolution = $request->get('resolution') ?? MetaFoxConstant::RESOLUTION_WEB;
        $driver     = resolve(DriverRepository::class)
            ->getDriver(Constants::DRIVER_TYPE_FORM, 'advertise.advertise_sponsor.store', $resolution);

        $form = resolve($driver);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], ['itemType' => $itemType, 'itemId' => $itemId]);
        }

        return $this->success($form->toArray($request));
    }

    /**
     * Feed Sponsor Form
     * @param Request $request
     * @param string  $itemType
     * @param int     $itemId
     * @urlParam itemId integer required Example: 545
     * @return JsonResponse
     */
    public function getFeedSponsorForm(Request $request, string $itemType, int $itemId): JsonResponse
    {
        $resolution = $request->get('resolution') ?? MetaFoxConstant::RESOLUTION_WEB;
        $driver     = resolve(DriverRepository::class)
            ->getDriver(Constants::DRIVER_TYPE_FORM, 'advertise.advertise_sponsor.feed.store', $resolution);

        $form = resolve($driver);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], ['itemType' => $itemType, 'itemId' => $itemId]);
        }

        return $this->success($form->toArray($request));
    }

    /**
     * @param UpdateTotalRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function updateTotalView(UpdateTotalRequest $request): JsonResponse
    {
        $data     = $request->validated();
        $itemType = Arr::get($data, 'item_type');
        $itemIds  = Arr::get($data, 'item_ids');
        $context  = user();

        $items = $this->repository->getMorphedItems($itemType, $itemIds);

        if (null === $items) {
            return $this->success([]);
        }

        $policy = $this->repository->getItemPolicy($itemType);

        $can = is_object($policy) && method_exists($policy, 'view');

        $items->each(function ($item) use ($context, $policy, $can) {
            if ($can && !policy_check(get_class($policy), 'view', $context, $item)) {
                return;
            }

            $this->repository->updateTotal($item);
        });

        return $this->success([]);
    }

    /**
     * @param UpdateTotalRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function updateTotalClick(UpdateTotalRequest $request): JsonResponse
    {
        $data     = $request->validated();
        $itemType = Arr::get($data, 'item_type');
        $itemIds  = Arr::get($data, 'item_ids');
        $context  = user();

        $items = $this->repository->getMorphedItems($itemType, $itemIds);

        if (null === $items) {
            return $this->success([]);
        }

        $policy = $this->repository->getItemPolicy($itemType);

        $can = is_object($policy) && method_exists($policy, 'view');

        $items->each(function ($item) use ($context, $policy, $can) {
            if ($can && !policy_check(get_class($policy), 'view', $context, $item)) {
                return;
            }

            $this->repository->updateTotal($item, 'total_click');
        });

        return $this->success([]);
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function active(Request $request, int $id): JsonResponse
    {
        $context = user();

        $isActive = (bool) match (MetaFox::isMobile()) {
            true  => $request->get('is_active', true),
            false => $request->get('active', true),
        };

        $sponsor = $this->repository->find(($id));

        policy_authorize(SponsorPolicy::class, 'update', $context, $sponsor);

        $this->repository->activeSponsor($sponsor, $isActive);

        $message = match ($isActive) {
            true  => __p('advertise::phrase.sponsor_successfully_activated'),
            false => __p('advertise::phrase.sponsor_successfully_deactivated'),
        };

        return $this->success([
            'id'        => $id,
            'is_active' => $isActive,
        ], [], $message);
    }
}
