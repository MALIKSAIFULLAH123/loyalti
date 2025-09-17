<?php

namespace MetaFox\Story\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\VideoServiceInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Story\Http\Requests\v1\Story\ArchiveRequest;
use MetaFox\Story\Http\Requests\v1\Story\IndexRequest;
use MetaFox\Story\Http\Requests\v1\Story\StoreRequest;
use MetaFox\Story\Http\Requests\v1\Story\ViewArchiveRequest;
use MetaFox\Story\Http\Resources\v1\Story\StoryArchiveCollection;
use MetaFox\Story\Http\Resources\v1\Story\StoryDetail;
use MetaFox\Story\Http\Resources\v1\Story\UserStory;
use MetaFox\Story\Http\Resources\v1\Story\UserStoryCollection;
use MetaFox\Story\Models\StorySet;
use MetaFox\Story\Policies\StoryPolicy;
use MetaFox\Story\Repositories\StoryRepositoryInterface;
use MetaFox\Story\Repositories\StorySetRepositoryInterface;
use MetaFox\Story\Support\Facades\StoryFacades;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Story\Http\Controllers\Api\StoryController::$controllers;.
 */

/**
 * Class StoryController.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class StoryController extends ApiController
{
    /**
     * StoryController Constructor.
     *
     * @param StoryRepositoryInterface    $storyRepository
     * @param StorySetRepositoryInterface $repository
     */
    public function __construct(
        protected StoryRepositoryInterface    $storyRepository,
        protected StorySetRepositoryInterface $repository,
    ) {
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     *
     * @return mixed
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request): UserStoryCollection
    {
        $params  = $request->validated();
        $context = user();
        $limit   = Arr::get($params, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        policy_authorize(StoryPolicy::class, 'viewAny', $context);

        $query = $this->repository->getStorySets($context, $params);
        $data  = $query->paginate($limit);

        return new UserStoryCollection($data);
    }

    /**
     * Browse item.
     *
     * @return mixed
     * @throws AuthenticationException
     */
    public function show(int $id): JsonResponse
    {
        $context = user();

        $data = $this->storyRepository->viewStory($context, $id);

        return $this->success(new StoryDetail($data));
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = $owner = user();
        $data    = $this->storyRepository->createStory($context, $owner, $params);

        $response = [];
        if (policy_check(StoryPolicy::class, 'viewAny', $context)) {
            $response = new UserStory($data->storySet);
        }

        $message = $data->in_process
            ? __p('story::phrase.story_video_in_process_message')
            : __p('story::phrase.story_created_successfully');

        return $this->success($response, [], $message);
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $context = user();

        $this->storyRepository->deleteStory($context, $id);

        return $this->success([
            'id' => $id,
        ], [], __p('story::phrase.story_deleted_successfully'));
    }

    /**
     * @throws AuthenticationException
     */
    public function archive(ArchiveRequest $request): JsonResponse
    {
        $params = $request->validated();
        $this->storyRepository->archive(user(), $params['story_id']);

        return $this->success([], [], __p('story::phrase.remove_photo_from_story_and_save_to_archive_successfully'));
    }

    /**
     * @param ViewArchiveRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function viewArchives(ViewArchiveRequest $request): JsonResponse
    {
        $context      = user();
        $params       = $request->validated();
        $data         = $this->storyRepository->viewStoryArchives($context, $params);
        $resources    = new StoryArchiveCollection($data);
        $responseData = $resources->toResponse($request)->getData(true);

        $meta         = Arr::get($responseData, 'meta', []);
        $responseData = Arr::get($responseData, 'data', []);
        $storyId      = Arr::get($params, 'story_id');
        $pos          = null;

        if ($storyId) {
            $pos = Arr::first(collect($responseData)->where('id', $storyId)->keys());
        }

        Arr::set($meta, 'pos', $pos);
        Arr::set($meta, 'next_date', $this->handleNextDate($context, $params));
        Arr::set($meta, 'prev_date', $this->handlePrevDate($context, $params));

        return $this->success($resources, $meta);
    }

    protected function handleNextDate($context, $params): ?string
    {
        Arr::set($params, 'operator', '<');
        Arr::set($params, 'sort_type', Browse::SORT_TYPE_DESC);
        Arr::set($params, 'date', Arr::pull($params, 'to_date'));

        return $this->storyRepository->getStoryArchiveByDate($context, $params)?->created_at;
    }

    protected function handlePrevDate($context, $params): ?string
    {
        Arr::set($params, 'operator', '>');
        Arr::set($params, 'sort_type', Browse::SORT_TYPE_ASC);
        Arr::set($params, 'date', Arr::pull($params, 'to_date'));

        return $this->storyRepository->getStoryArchiveByDate($context, $params)?->created_at;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function autoArchive(Request $request): JsonResponse
    {
        $params = $request->all();
        Arr::set($params, 'expired_at', 0);

        $model = $this->repository->getModel()->newQuery()->firstWhere('user_id', user()->entityId());
        if ($model instanceof StorySet) {
            Arr::set($params, 'expired_at', $model->expired_at);
        }

        $this->repository->createStorySet(user(), $params);

        return $this->success([], [], __p('core::phrase.updated_successfully'));
    }

    public function callback(string $provider, Request $request): JsonResponse
    {
        $response = [];

        try {
            $service = StoryFacades::getVideoServiceClassByDriver($provider);
            if ($service instanceof VideoServiceInterface) {
                $response = $service->handleWebhook($request);
            }
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }

        return $this->success([
            'success' => $response,
        ], [], '');
    }
}
