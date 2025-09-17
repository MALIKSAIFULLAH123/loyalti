<?php

namespace MetaFox\Forum\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Forum\Http\Requests\v1\Forum\IndexRequest;
use MetaFox\Forum\Http\Resources\v1\Forum\ForumDetail;
use MetaFox\Forum\Http\Resources\v1\Forum\ForumItemCollection;
use MetaFox\Forum\Http\Resources\v1\Forum\ForumQuickNavigationCollection;
use MetaFox\Forum\Http\Resources\v1\ForumPost\ForumPostItemCollection;
use MetaFox\Forum\Http\Resources\v1\ForumThread\ForumThreadCollection;
use MetaFox\Forum\Policies\ForumPolicy;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Forum\Support\ForumSupport;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Support\Browse\Browse;

class ForumController extends ApiController
{
    public ForumRepositoryInterface $repository;

    public function __construct(ForumRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function show(int $id): JsonResponse
    {
        $forum = $this->repository->find($id);

        $context = user();

        policy_authorize(ForumPolicy::class, 'viewAny', $context);

        return $this->success(new ForumDetail($forum));
    }

    /**
     * @param  IndexRequest            $request
     * @return mixed
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();

        $context = user();

        $view = $params['view'];

        if ($view == ForumSupport::VIEW_QUICK_NAVIGATION) {
            if (!policy_check(ForumPolicy::class, 'viewAny', $context)) {
                return $this->success();
            }

            $items = $this->repository->paginateForums($params);

            return new ForumQuickNavigationCollection($items);
        }

        switch ($view) {
            case ForumSupport::VIEW_SUB_FORUMS:
                //@Todo: after mobile handle this then delete
                if (!policy_check(ForumPolicy::class, 'viewAny', $context)) {
                    return $this->success();
                }

                $items = $this->repository->getSubForums($context, $params['forum_id']);
                $data  = new ForumItemCollection($items);

                break;
            case Browse::VIEW_SEARCH:
                Arr::set($params, 'view', Browse::VIEW_SEARCH);

                $searchTag = Arr::get($params, 'tag');

                if ($searchTag != null && $params['item_type'] == ForumSupport::SEARCH_BY_POST) {
                    return $this->success();
                }

                $items = $this->repository->getSearchItems($context, $params);

                $data = match ($params['item_type']) {
                    ForumSupport::SEARCH_BY_THREAD => new ForumThreadCollection($items),
                    // no break
                    default => new ForumPostItemCollection($items),
                };

                break;
            default:
                if (!policy_check(ForumPolicy::class, 'viewAny', $context)) {
                    return $this->success();
                }

                $items = $this->repository->viewForums($context, $params);

                $data  = collect($items);

                break;
        }

        return $this->success($data);
    }

    /**
     * @throws AuthenticationException
     */
    public function getSubForums(Request $request, int $id)
    {
        $limit   = $request->get('limit', 4);
        $context = user();
        $forum   = $this->repository->find($id);
        $items   = $this->repository->getSubForums($context, $id, $limit);

        return $this->success(new ForumItemCollection($items), [
            'title'       => $forum->toTitle(),
            'description' => $forum->description,
        ]);
    }

    public function getOptions(IndexRequest $request): JsonResponse
    {
        $context = user();
        $params  = $request->validated();

        if (!policy_check(ForumPolicy::class, 'viewAny', $context)) {
            return $this->success([]);
        }

        $items = $this->repository->viewForums($context, $params);

        $collection  = collect($items)->map(function ($item) {
            return [
                'id'            => Arr::get($item, 'id'),
                'name'          => Arr::get($item, 'name'),
                'module_name'   => 'forum',
                'resource_name' => 'forum_thread_category',
            ];
        });

        return $this->success($collection->toArray());
    }
}
