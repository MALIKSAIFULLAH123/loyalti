<?php

namespace MetaFox\Forum\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Forum\Http\Requests\v1\ForumPost\CreateFormRequest;
use MetaFox\Forum\Http\Requests\v1\ForumPost\IndexRequest;
use MetaFox\Forum\Http\Requests\v1\ForumPost\PosterRequest;
use MetaFox\Forum\Http\Requests\v1\ForumPost\QuoteRequest;
use MetaFox\Forum\Http\Requests\v1\ForumPost\StoreRequest;
use MetaFox\Forum\Http\Requests\v1\ForumPost\UpdateRequest;
use MetaFox\Forum\Http\Resources\v1\ForumPost\CreateForm;
use MetaFox\Forum\Http\Resources\v1\ForumPost\EditForm;
use MetaFox\Forum\Http\Resources\v1\ForumPost\ForumPostDetail;
use MetaFox\Forum\Http\Resources\v1\ForumPost\ForumPostItem as Detail;
use MetaFox\Forum\Http\Resources\v1\ForumPost\ForumPostItemCollection as ItemCollection;
use MetaFox\Forum\Http\Resources\v1\ForumPost\QuoteForm;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Policies\ForumPostPolicy;
use MetaFox\Forum\Repositories\ForumPostRepositoryInterface;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use MetaFox\Platform\Exceptions\PermissionDeniedException;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityCollection;
use MetaFox\User\Support\Facades\UserEntity;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Forum\Http\Controllers\Api\ForumPostController::$controllers.
 */

/**
 * Class ForumPostController.
 */
class ForumPostController extends ApiController
{
    /**
     * @var ForumPostRepositoryInterface
     */
    public $repository;

    /**
     * @var ForumThreadRepositoryInterface
     */
    public $threadRepository;

    public function __construct(
        ForumPostRepositoryInterface $repository,
        ForumThreadRepositoryInterface $threadRepository
    ) {
        $this->repository       = $repository;
        $this->threadRepository = $threadRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();

        $user = $owner = user();

        $ownerId = Arr::get($params, 'user_id');

        if ($ownerId) {
            $owner = UserEntity::getById($ownerId)->detail;
        }

        $items = $this->repository->viewPosts($user, $owner, $params);

        return new ItemCollection($items);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws PermissionDeniedException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = $owner = user();

        app('flood')->checkFloodControlWhenCreateItem($context, ForumPost::ENTITY_TYPE);

        if ($params['owner_id'] === 0) {
            $thread = $this->threadRepository->find($params['thread_id']);
            if ($context->entityId() != $thread['owner_id']) {
                $owner = UserEntity::getById($thread['owner_id'])->detail;
            }
        }

        $data = $this->repository->createPost($context, $owner, $params);

        $message = __p(
            'core::phrase.resource_create_success',
            ['resource_name' => __p('forum::phrase.post')]
        );

        $response = new ForumPostDetail($data);
        if (!$data->isApproved()) {
            $message = __p('forum::phrase.post_has_been_submitted_to_admins_for_approval');

            if (!MetaFox::isMobile()) {
                return $this->success([], [], $message);
            }
        }

        return $this->success($response, [], $message);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Detail
     */
    public function show(int $id)
    {
        $user = user();
        $data = $this->repository->viewPost($user, $id);

        return new Detail($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRequest           $request
     * @param  int                     $id
     * @return Detail
     * @throws ValidatorException
     * @throws AuthenticationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params  = $request->validated();
        $context = user();

        $data = $this->repository->updatePost($context, $id, $params);

        return $this->success(new Detail($data), [], __p('forum::phrase.post_edited_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $context = user();
        $this->repository->deletePost($context, $id);

        return $this->success([
            'id' => $id,
        ], [], __p('forum::phrase.post_deleted_successfully'));
    }

    /**
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function approve(int $id): JsonResponse
    {
        $context = user();

        $resource = $this->repository->approve($context, $id);

        return $this->success(new ForumPostDetail($resource), [], __p('forum::phrase.post_successfully_approved'));
    }

    /**
     * @param  CreateFormRequest       $request
     * @param  int|null                $id
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function form(CreateFormRequest $request, ?int $id = null): JsonResponse
    {
        $post    = new ForumPost();
        $params  = $request->validated();
        $context = user();

        if ($id != null) {
            $post = $this->repository->find($id);
            policy_authorize(ForumPostPolicy::class, 'update', $context, $post);

            return $this->success(new EditForm($post));
        }

        $thread = $this->threadRepository->find($params['thread_id']);

        policy_authorize(ForumPostPolicy::class, 'reply', $context, $thread);

        $post->owner_id = $params['owner_id'];

        $post->thread_id = $params['thread_id'];

        return $this->success(new CreateForm($post));
    }

    /**
     * @param  int          $id
     * @return JsonResponse
     */
    public function getQuoteForm(int $id): JsonResponse
    {
        $context = user();

        $quotePost = $this->repository->find($id);

        policy_authorize(ForumPostPolicy::class, 'quote', $context, $quotePost);

        $form = new QuoteForm($quotePost);

        return $this->success($form);
    }

    /**
     * @param  QuoteRequest $request
     * @return JsonResponse
     */
    public function quote(QuoteRequest $request): JsonResponse
    {
        $data = $request->validated();

        $context = user();

        app('flood')->checkFloodControlWhenCreateItem($context, ForumPost::ENTITY_TYPE);

        $quotePost = $this->repository->quotePost($context, $data);

        $resource = ResourceGate::asResource($quotePost, 'item');

        $message = __p('forum::phrase.quoted_successfully');

        if (!$quotePost->isApproved()) {
            $message  = __p('forum::phrase.post_has_been_submitted_to_admins_for_approval');

            if (!MetaFox::isMobile()) {
                $resource = null;
            }
        }

        return $this->success($resource, [], $message);
    }

    public function getPosters(PosterRequest $request): JsonResponse
    {
        $params = $request->validated();

        $threadId = Arr::get($params, 'thread_id', 0);

        $context = user();

        $posters = $this->repository->viewPosters($context, $threadId, $params);

        return $this->success(new UserEntityCollection($posters));
    }
}
