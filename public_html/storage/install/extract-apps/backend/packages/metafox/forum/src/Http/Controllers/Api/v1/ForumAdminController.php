<?php

namespace MetaFox\Forum\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Forum\Http\Requests\v1\Forum\Admin\DeleteRequest;
use MetaFox\Forum\Http\Requests\v1\Forum\Admin\IndexRequest;
use MetaFox\Forum\Http\Requests\v1\Forum\Admin\SearchModeratorRequest;
use MetaFox\Forum\Http\Requests\v1\Forum\Admin\SetupModeratorRequest;
use MetaFox\Forum\Http\Requests\v1\Forum\Admin\SetupPermissionRequest;
use MetaFox\Forum\Http\Requests\v1\Forum\Admin\StoreRequest;
use MetaFox\Forum\Http\Requests\v1\Forum\Admin\UpdateRequest;
use MetaFox\Forum\Http\Resources\v1\Forum\Admin\CreateForumForm;
use MetaFox\Forum\Http\Resources\v1\Forum\Admin\EditForumForm;
use MetaFox\Forum\Http\Resources\v1\Forum\ForumDetail as Detail;
use MetaFox\Forum\Http\Resources\v1\Forum\ForumItemCollection as ItemCollection;
use MetaFox\Forum\Http\Resources\v1\Moderator\Admin\ModeratorItemCollection;
use MetaFox\Forum\Policies\ForumPolicy;
use MetaFox\Forum\Repositories\ForumAdminRepositoryInterface;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Forum\Repositories\ModeratorRepositoryInterface;
use MetaFox\Forum\Repositories\PermissionConfigRepositoryInterface;
use MetaFox\Forum\Repositories\UserRolePermissionRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Models\User;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Forum\Http\Controllers\Api\ForumAdminController::$controllers;.
 */

/**
 * Class ForumAdminController.
 */
class ForumAdminController extends ApiController
{
    public function __construct(
        public ForumAdminRepositoryInterface $adminRepository,
        public ModeratorRepositoryInterface $moderatorRepository
    ) {
    }

    /**
     * Display a listing of the resource.
     *
     * @param  IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request)
    {
        $context = user();

        $params = $request->validated();

        $data = $this->adminRepository->viewForumsInAdminCP($context, $params);

        return new ItemCollection($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     *
     * @return Detail
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $data = $this->adminRepository->createForum($context, $params);

        $parentId = $data->parent_id;
        $url      = '/forum/forum/browse';
        if ($parentId) {
            $url = "/forum/forum/$parentId/forum/browse?parent_id=$parentId";
        }

        Artisan::call('cache:reset');

        return $this->success(new Detail($data), [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url' => $url,
                ],
            ],
        ], __p('forum::phrase.forum_created_successfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Detail
     */
    public function show($id)
    {
        $data = $this->adminRepository->find($id);

        return new Detail($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRequest      $request
     * @param  int                $id
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, $id): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $data = $this->adminRepository->updateForum($context, $id, $params);

        Artisan::call('cache:reset');

        return $this->success(new Detail($data), [], __p('forum::phrase.forum_updated_successfully'));
    }

    /**
     * @param  DeleteRequest $request
     * @return JsonResponse
     */
    public function deleteForum(DeleteRequest $request): JsonResponse
    {
        $data = $request->validated();

        $context = user();

        $this->adminRepository->deleteForum(
            $context,
            Arr::get($data, 'id'),
            Arr::get($data, 'delete_option'),
            Arr::get($data, 'alternative_id')
        );

        return $this->success([], [], __p('forum::phrase.forum_deleted_successfully'));
    }

    /**
     * @param  int          $id
     * @return JsonResponse
     */
    public function edit(int $id): JsonResponse
    {
        $forum = $this->adminRepository->find($id);

        $context = user();

        policy_authorize(ForumPolicy::class, 'update', $context, $forum);

        $form = new EditForumForm($forum);

        return $this->success($form);
    }

    public function create(Request $request): JsonResponse
    {
        $form = new CreateForumForm();

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        return $this->success($form);
    }

    /**
     * @param  Request                 $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function order(Request $request): JsonResponse
    {
        $orderIds = $request->get('order_ids');

        $context = user();

        $this->adminRepository->order($context, $orderIds);

        return $this->success([], [], __p('forum::phrase.forums_successfully_ordered'));
    }

    public function toggleActive(int $id, Request $request): JsonResponse
    {
        $closed = (bool) $request->get('active', false);

        $context = user();

        $forum = $this->adminRepository->close($context, $id, $closed);

        $message = match ($closed) {
            true  => __p('forum::phrase.forum_successfully_closed'),
            false => __p('forum::phrase.forum_successfully_reopened'),
        };

        $meta = [];

        if ($closed && $forum->level == 1) {
            $total = $this->adminRepository->countActiveForumByLevel($forum->level);

            if ($total == 0) {
                $meta = [
                    'alert' => [
                        'title'   => __p('forum::phrase.close_forum_warning_title'),
                        'message' => __p('forum::phrase.close_forum_warning_desc'),
                    ],
                ];
            }
        }

        return $this->success([], $meta, $message);
    }

    public function setupForumModerators(SetupModeratorRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        $context = user();

        $forum = resolve(ForumRepositoryInterface::class)->find($id);

        if (resolve(PermissionConfigRepositoryInterface::class)->updateConfigs($context, $forum, $data)) {
            return $this->success([], [], __p('forum::phrase.moderator_setup_successfully'));
        }

        return $this->error(__p('forum::phrase.moderator_setup_failed'));
    }

    public function setupForumPermissions(SetupPermissionRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        $context = user();

        $forum = resolve(ForumRepositoryInterface::class)->find($id);

        if (resolve(UserRolePermissionRepositoryInterface::class)->updateRolePermission($context, $forum, $data)) {
            return $this->success([], [], __p('forum::phrase.permission_setup_successfully'));
        }

        return $this->error(__p('forum::phrase.permission_setup_failed'));
    }

    public function searchModerator(SearchModeratorRequest $request): JsonResponse
    {
        $data = $request->validated();

        $context = user();

        $moderators = $this->moderatorRepository->searchModerators($context, $data);

        return $this->success(new ModeratorItemCollection($moderators));
    }
}
