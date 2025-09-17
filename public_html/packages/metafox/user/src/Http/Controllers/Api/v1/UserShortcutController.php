<?php

namespace MetaFox\User\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Http\Requests\v1\UserShortcut\IndexRequest;
use MetaFox\User\Http\Requests\v1\UserShortcut\UpdateRequest;
use MetaFox\User\Http\Resources\v1\UserShortcut\UserShortcutItem;
use MetaFox\User\Http\Resources\v1\UserShortcut\UserShortcutItemCollection as ItemCollection;
use MetaFox\User\Policies\UserShortcutPolicy;
use MetaFox\User\Repositories\UserShortcutRepositoryInterface;
use MetaFox\User\Support\Facades\UserEntity;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\User\Http\Controllers\Api\UserShortcutController::$controllers.
 */

/**
 * Class UserShortcutController.
 * @ignore
 * @codeCoverageIgnore
 * @group user
 * @authenticated
 * @admincp
 */
class UserShortcutController extends ApiController
{
    /**
     * @var UserShortcutRepositoryInterface
     */
    public UserShortcutRepositoryInterface $repository;

    /**
     * UserShortcutController constructor.
     *
     * @param UserShortcutRepositoryInterface $repository
     */
    public function __construct(UserShortcutRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse shortcuts.
     *
     * @param IndexRequest $request
     *
     * @return ItemCollection<UserShortcutItem>
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @group user/shortcut
     * @authenticated
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params  = $request->validated();
        $context = user();

        policy_authorize(UserShortcutPolicy::class, 'viewAny', $context);

        $data = $this->repository->viewShortcuts($context, $params);

        return new ItemCollection($data);
    }

    /**
     * View shortcut.
     *
     * @param IndexRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @group user/shortcut
     * @authenticated
     */
    public function viewForEdit(IndexRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->viewForEdit(user(), $params);

        return $this->success(new ItemCollection($data));
    }

    /**
     * Update shortcut.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws ValidatorException
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @group user/shortcut
     * @authenticated
     */
    public function manage(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $content = UserEntity::getById($id)->detail;
        $this->repository->updateShortType(user(), $content, $params['sort_type']);

        $data = $this->repository->viewForEdit(user(), $params);

        return $this->success(new ItemCollection($data));
    }
}
