<?php

namespace MetaFox\Activity\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Activity\Http\Requests\v1\Type\Admin\IndexRequest;
use MetaFox\Activity\Http\Requests\v1\Type\Admin\StoreRequest;
use MetaFox\Activity\Http\Requests\v1\Type\Admin\UpdateRequest;
use MetaFox\Activity\Http\Resources\v1\Type\Admin\TypeDetail as Detail;
use MetaFox\Activity\Http\Resources\v1\Type\Admin\TypeItem;
use MetaFox\Activity\Http\Resources\v1\Type\Admin\TypeItemCollection as ItemCollection;
use MetaFox\Activity\Http\Resources\v1\Type\Admin\UpdateTypeForm;
use MetaFox\Activity\Repositories\TypeRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Support\Str;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Activity\Http\Controllers\Api\TypeAdminController::$controllers.
 */

/**
 * Class TypeAdminController.
 * @group admin/feed
 * @authenticated
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 */
class TypeAdminController extends ApiController
{
    /**
     * @var TypeRepositoryInterface
     */
    public TypeRepositoryInterface $repository;

    /**
     * @param TypeRepositoryInterface $repository
     */
    public function __construct(TypeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse Type.
     *
     * @return ItemCollection<TypeItem>
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $context = user();
        $params  = $request->validated();
        $data    = $this->repository->viewTypes($context, $params);

        return new ItemCollection($data);
    }

    /**
     * Create type.
     *
     * @param StoreRequest $request
     *
     * @return Detail
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): Detail
    {
        $params = $request->validated();

        $data = $this->repository->create($params);

        return new Detail($data);
    }

    /**
     * View type.
     *
     * @param int $id
     *
     * @return Detail
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->find($id);

        return new Detail($data);
    }

    public function edit($id): JsonResponse
    {
        $item = $this->repository->find($id);

        $form = new UpdateTypeForm($item);

        return $this->success($form);
    }

    /**
     * Update type.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws ValidatorException
     * @throws AuthenticationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $data = $this->repository->updateType(user(), $id, $params);

        return $this->success(new Detail($data), [], __p('activity::admin.activity_type_successfully_updated'));
    }

    /**
     * Delete type.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->success([
            'id' => $id,
        ]);
    }

    /**
     * Update active status.
     * @param  ActiveRequest $request
     * @param  int           $id
     * @return JsonResponse
     */
    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $context = user();
        $params  = $request->validated();
        $active  = $params['active'] ? 1 : 0;

        $type = $this->repository->updateType($context, $id, ['is_active' => $active]);

        return $this->success(new Detail($type), [], __p('activity::phrase.activity_type_toggle_state_successfully', ['state' => $active]));
    }

    /**
     * Update create feed permission.
     * @param  ActiveRequest $request
     * @param  int           $id
     * @return JsonResponse
     */
    public function toggleAbilityActive(ActiveRequest $request, int $id, string $ability): JsonResponse
    {
        $context = user();
        $ability = Str::snake($ability);
        $params  = $request->validated();
        $active  = $params['active'] ? 1 : 0;

        $type = $this->repository->updateType($context, $id, [$ability => $active]);

        return $this->success(new Detail($type), [], __p('activity::admin.activity_type_successfully_updated'));
    }
}
