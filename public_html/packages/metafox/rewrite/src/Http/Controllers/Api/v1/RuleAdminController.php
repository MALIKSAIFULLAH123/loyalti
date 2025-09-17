<?php

namespace MetaFox\Rewrite\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Rewrite\Http\Requests\v1\Rule\Admin\IndexRequest;
use MetaFox\Rewrite\Http\Requests\v1\Rule\Admin\StoreRequest;
use MetaFox\Rewrite\Http\Requests\v1\Rule\Admin\UpdateRequest;
use MetaFox\Rewrite\Http\Resources\v1\Rule\Admin\RuleDetail as Detail;
use MetaFox\Rewrite\Http\Resources\v1\Rule\Admin\RuleItemCollection as ItemCollection;
use MetaFox\Rewrite\Http\Resources\v1\Rule\Admin\StoreRuleForm;
use MetaFox\Rewrite\Http\Resources\v1\Rule\Admin\UpdateRuleForm;
use MetaFox\Rewrite\Repositories\RuleRepositoryInterface;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Core\Http\Controllers\Api\RuleAdminController::$controllers.
 */

/**
 * Class RuleAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class RuleAdminController extends ApiController
{
    /**
     * @var RuleRepositoryInterface
     */
    private RuleRepositoryInterface $repository;

    /**
     * RuleAdminController constructor.
     *
     * @param RuleRepositoryInterface $repository
     */
    public function __construct(RuleRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     *
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->paginate($params['limit'] ?? 100);

        return new ItemCollection($data);
    }

    /**
     * Create item.
     *
     * @param StoreRequest $request
     *
     * @return Detail
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): Detail
    {
        $params = $request->validated();
        $data   = $this->repository->create($params);

        return new Detail($data);
    }

    public function create(): JsonResponse
    {
        return $this->success(new StoreRuleForm());
    }

    /**
     * View item.
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

    /**
     * Update item.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->update($params, $id);

        return $this->success(new Detail($data), [], __p('core::phrase.already_saved_changes'));
    }

    /**
     * Remove item.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $entry = $this->repository->find($id);

        $entry->delete();

        return $this->success([
            'id' => $id,
        ], [], __p('core::phrase.already_saved_changes'));
    }

    /**
     * View creation form.
     *
     * @return StoreRuleForm
     */
    public function formStore(): StoreRuleForm
    {
        return new StoreRuleForm();
    }

    /**
     * View updating form.
     *
     * @param int $id
     *
     * @return UpdateRuleForm
     */
    public function formUpdate(int $id): UpdateRuleForm
    {
        $resource = $this->repository->find($id)->first();

        return new UpdateRuleForm($resource);
    }
}
