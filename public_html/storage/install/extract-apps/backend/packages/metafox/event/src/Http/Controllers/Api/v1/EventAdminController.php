<?php

namespace MetaFox\Event\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Event\Http\Requests\v1\Event\Admin\BatchUpdateRequest;
use MetaFox\Event\Http\Requests\v1\Event\Admin\IndexRequest;
use MetaFox\Event\Http\Resources\v1\Event\Admin\EventItemCollection as ItemCollection;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Repositories\EventAdminRepositoryInterface;
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
 * | @link \MetaFox\Event\Http\Controllers\Api\EventAdminController::$controllers;
 */

/**
 * Class EventAdminController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group event
 * @admincp
 */
class EventAdminController extends ApiController
{
    public function __construct(protected EventAdminRepositoryInterface $repository) {}

    /**
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();
        $limit  = Arr::get($params, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        $data   = $this->repository->viewEvents(user(), $params)->paginate($limit, ['events.*']);

        return new ItemCollection($data);
    }

    /**
     * @param SponsorRequest $request
     * @param int            $id
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     */
    public function sponsor(SponsorRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $sponsor = $params['sponsor'];

        $context = user();

        $this->repository->sponsor($context, $id, $sponsor);

        $event = $this->repository->find($id);

        $isSponsor = (bool) $sponsor;

        $isPendingSponsor = $isSponsor && !$event->is_sponsor;

        $message = $isPendingSponsor ? 'core::phrase.resource_sponsored_successfully_please_waiting_for_approval' : ($isSponsor ? 'core::phrase.resource_sponsored_successfully' : 'core::phrase.resource_unsponsored_successfully');

        return $this->success([], [], __p($message, ['resource_name' => __p('event::phrase.event')]));
    }


    /**
     * Sponsor event in feed.
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
        $params = $request->validated();

        $sponsor = $params['sponsor'];

        $this->repository->sponsorInFeed(user(), $id, $sponsor);

        $isSponsor        = (bool) $sponsor;
        $event            = $this->repository->find($id);
        $isPendingSponsor = $isSponsor && !$event->sponsor_in_feed;
        $message          = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_in_feed_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_in_feed_successfully'
                : 'core::phrase.resource_unsponsored_in_feed_successfully');

        return $this->success([], [], __p($message, ['resource_name' => __p('event::phrase.event')]));
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

        return $this->success([], [], __p('event::phrase.event_s_has_been_approved'));
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
        $params = $request->validated();
        $ids    = Arr::get($params, 'id', []);

        if (!user()->hasPermissionTo('event.moderate')) {
            throw new AuthorizationException();
        }

        $query = $this->repository->getModel()->newQuery();

        $query->whereIn('id', $ids)
            ->get()
            ->each(function (Event $model) {
                $model->delete();
            });

        return $this->success([], [], __p('event::phrase.event_s_deleted_successfully'));
    }
}
