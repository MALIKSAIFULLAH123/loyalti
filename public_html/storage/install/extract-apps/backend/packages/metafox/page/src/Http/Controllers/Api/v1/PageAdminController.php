<?php

namespace MetaFox\Page\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Page\Http\Requests\v1\Page\Admin\BatchUpdateRequest;
use MetaFox\Page\Http\Requests\v1\Page\Admin\IndexRequest;
use MetaFox\Page\Http\Resources\v1\Page\Admin\PageItemCollection as ItemCollection;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Page\Repositories\PageAdminRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\SponsorRequest;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Page\Http\Controllers\Api\PageAdminController::$controllers;.
 */

/**
 * Class PageAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class PageAdminController extends ApiController
{
    public function __construct(protected PageAdminRepositoryInterface $repository)
    {
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest           $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params  = $request->validated();
        $context = user();
        $limit   = Arr::get($params, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        policy_authorize(PagePolicy::class, 'viewAny', $context);

        $data = $this->repository->viewPages($context, $params)->paginate($limit, ['pages.*']);

        return new ItemCollection($data);
    }

    /**
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

        $page = $this->repository->find($id);

        $isSponsor        = (bool) $sponsor;
        $isPendingSponsor = $isSponsor && !$page->is_sponsor;

        $message = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_successfully'
                : 'core::phrase.resource_unsponsored_successfully');

        return $this->success([], [], __p($message, ['resource_name' => __p('page::phrase.page')]));
    }

    /**
     * @param  BatchUpdateRequest      $request
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

        return $this->success([], [], __p('page::phrase.page_s_approved_successfully'));
    }

    /**
     * @param  BatchUpdateRequest      $request
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function batchDelete(BatchUpdateRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $ids     = Arr::get($params, 'id', []);
        $context = user();
        if (!$context->hasPermissionTo('page.moderate')) {
            throw new AuthorizationException();
        }

        foreach ($ids as $id) {
            $this->repository->deletePage($context, $id);
        }

        return $this->success([], [], __p('page::phrase.successfully_deleted_the_page_s'));
    }
}
