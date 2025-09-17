<?php

namespace MetaFox\Blog\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Blog\Http\Requests\v1\Blog\Admin\BatchUpdateRequest;
use MetaFox\Blog\Http\Requests\v1\Blog\Admin\IndexRequest;
use MetaFox\Blog\Http\Resources\v1\Blog\Admin\BlogItemCollection as ItemCollection;
use MetaFox\Blog\Models\Blog;
use MetaFox\Blog\Policies\BlogPolicy;
use MetaFox\Blog\Repositories\BlogAdminRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\SponsorInFeedRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorRequest;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * | stub: /packages/controllers/admin_api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Blog\Http\Controllers\Api\BlogController::$controllers;
 */

/**
 * Class BlogAdminController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group blog
 */
class BlogAdminController extends ApiController
{

    /**
     * @param BlogAdminRepositoryInterface $repository
     */
    public function __construct(protected BlogAdminRepositoryInterface $repository) {}

    /**
     * Browse blogs.
     *
     * @param IndexRequest $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request)
    {
        $params  = $request->validated();
        $context = user();

        policy_authorize(BlogPolicy::class, 'viewAny', $context);

        $data = $this->repository->viewBlogs($context, $params);

        return new ItemCollection($data);
    }

    /**
     * Sponsor blog.
     *
     * @param SponsorRequest $request
     * @param int            $id
     *
     * @return JsonResponse
     * @throws AuthenticationException|AuthorizationException
     */
    public function sponsor(SponsorRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $sponsor = $params['sponsor'];

        $this->repository->sponsor(user(), $id, $sponsor);

        $blog = $this->repository->find($id);

        $isSponsor = (bool) $sponsor;

        $isPendingSponsor = $isSponsor && !$blog->is_sponsor;

        $message = $isPendingSponsor ? 'core::phrase.resource_sponsored_successfully_please_waiting_for_approval' : ($isSponsor ? 'core::phrase.resource_sponsored_successfully' : 'core::phrase.resource_unsponsored_successfully');

        return $this->success([], [], __p($message, ['resource_name' => __p('blog::phrase.blog')]));
    }

    /**
     * Sponsor blog in feed.
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
        $blog             = $this->repository->find($id);
        $isPendingSponsor = $isSponsor && !$blog->sponsor_in_feed;
        $message          = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_in_feed_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_in_feed_successfully'
                : 'core::phrase.resource_unsponsored_in_feed_successfully');

        return $this->success([], [], __p($message, ['resource_name' => __p('blog::phrase.blog')]));
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

        return $this->success([], [], __p('blog::phrase.blog_s_has_been_approved'));
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

        if (!user()->hasPermissionTo('blog.moderate')) {
            throw new AuthorizationException();
        }

        $query = $this->repository->getModel()->newQuery();

        $query->whereIn('id', $ids)
            ->get()
            ->each(function (Blog $model) {
                $model->delete();
            });

        return $this->success([], [], __p('blog::phrase.blog_s_was_deleted_successfully'));
    }
}
