<?php

namespace MetaFox\Group\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Group\Http\Requests\v1\Group\Admin\BatchUpdateRequest;
use MetaFox\Group\Http\Requests\v1\Group\Admin\IndexRequest;
use MetaFox\Group\Http\Resources\v1\Group\Admin\GroupItemCollection as ItemCollection;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\GroupAdminRepositoryInterface;
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
 * | @link \MetaFox\Group\Http\Controllers\Api\GroupAdminController::$controllers;
 */

/**
 * Class GroupAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class GroupAdminController extends ApiController
{
    /**
     * GroupAdminController Constructor
     *
     * @param GroupAdminRepositoryInterface $repository
     */
    public function __construct(protected GroupAdminRepositoryInterface $repository) {}

    /**
     * Browse item
     *
     * @param IndexRequest $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params  = $request->validated();
        $context = user();
        $limit   = Arr::get($params, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        policy_authorize(GroupPolicy::class, 'viewAny', $context);

        $data = $this->repository->viewGroups($context, $params)
            ->paginate($limit, ['groups.*']);

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

        $this->repository->sponsor(user(), $id, $sponsor);

        $group = $this->repository->find($id);

        $isSponsor = (bool) $sponsor;

        $isPendingSponsor = $isSponsor && !$group->is_sponsor;

        $message = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_successfully'
                : 'core::phrase.resource_unsponsored_successfully');

        return $this->success([], [], __p($message, ['resource_name' => __p('group::phrase.sponsor_setting_group')]));
    }

    /**
     * @param SponsorInFeedRequest $request
     * @param int                  $id
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
        $group            = $this->repository->find($id);
        $isPendingSponsor = $isSponsor && !$group->sponsor_in_feed;
        $message          = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_in_feed_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_in_feed_successfully'
                : 'core::phrase.resource_unsponsored_in_feed_successfully');

        return $this->success([], [], __p($message, ['resource_name' => __p('group::phrase.sponsor_setting_group')]));
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

        return $this->success([], [], __p('group::phrase.group_s_has_been_approved'));
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

        if (!$context->hasPermissionTo('group.moderate')) {
            throw new AuthorizationException();
        }

        foreach ($ids as $id) {
            $this->repository->deleteGroup($context, $id);
        }

        return $this->success([], [], __p('group::phrase.successfully_deleted_the_group_s'));
    }
}
