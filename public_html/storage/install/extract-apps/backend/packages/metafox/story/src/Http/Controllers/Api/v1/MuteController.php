<?php

namespace MetaFox\Story\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Story\Http\Requests\v1\Mute\IndexRequest;
use MetaFox\Story\Http\Requests\v1\Mute\StoreRequest;
use MetaFox\Story\Http\Requests\v1\Mute\UnmuteRequest;
use MetaFox\Story\Http\Resources\v1\Mute\MuteDetail as Detail;
use MetaFox\Story\Http\Resources\v1\Mute\MuteItemCollection as ItemCollection;
use MetaFox\Story\Repositories\MuteRepositoryInterface;
use MetaFox\User\Support\Facades\UserEntity;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Story\Http\Controllers\Api\MuteController::$controllers;
 */

/**
 * Class MuteController
 *
 * @codeCoverageIgnore
 * @ignore
 */
class MuteController extends ApiController
{
    /**
     * @var MuteRepositoryInterface
     */
    private MuteRepositoryInterface $repository;

    /**
     * MuteController Constructor
     *
     * @param MuteRepositoryInterface $repository
     */
    public function __construct(MuteRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item
     *
     * @param IndexRequest $request
     *
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->viewMuted(user(), $params)
            ->paginate($params['limit'] ?? 100);

        return new ItemCollection($data);
    }

    /**
     * Store item
     *
     * @param StoreRequest $request
     *
     * @return Detail
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->mute(user(), $params);

        return $this->success([
            'id' => $data->ownerId(),
        ], [], __p('story::phrase.user_name_stories_was_muted_successfully', [
            'user_name' => $data->owner?->full_name,
        ]));
    }

    public function unmute(UnmuteRequest $request): JsonResponse
    {
        $params = $request->validated();
        $userId = Arr::get($params, 'user_id');

        $this->repository->unmute(user(), $params);

        $user = UserEntity::getById($userId)?->detail;

        return $this->success([
            'user_id' => $userId,
        ], [], __p('story::phrase.user_name_stories_was_unmuted_successfully', [
            'user_name' => $user?->full_name,
        ]));
    }

    /**
     * Delete item
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteMuted(user(), $id);

        return $this->success([
            'id' => $id,
        ], [], __p('story::phrase.unmuted_successfully'));
    }
}
