<?php

namespace MetaFox\User\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Form\AbstractForm;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Http\Requests\v1\UserPromotion\Admin\IndexRequest;
use MetaFox\User\Http\Requests\v1\UserPromotion\Admin\StoreRequest;
use MetaFox\User\Http\Requests\v1\UserPromotion\Admin\UpdateRequest;
use MetaFox\User\Http\Resources\v1\UserPromotion\Admin\CreateForm;
use MetaFox\User\Http\Resources\v1\UserPromotion\Admin\EditForm;
use MetaFox\User\Http\Resources\v1\UserPromotion\Admin\UserPromotionDetail as Detail;
use MetaFox\User\Http\Resources\v1\UserPromotion\Admin\UserPromotionItem;
use MetaFox\User\Http\Resources\v1\UserPromotion\Admin\UserPromotionItemCollection as ItemCollection;
use MetaFox\User\Models\UserPromotion;
use MetaFox\User\Repositories\UserPromotionRepositoryInterface;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\User\Http\Controllers\Api\UserPromotionAdminController::$controllers.
 */

/**
 * Class UserPromotionAdminController.
 * @ignore
 * @codeCoverageIgnore
 * @group user
 * @authenticated
 * @admincp
 */
class UserPromotionAdminController extends ApiController
{
    /**
     * @var UserPromotionRepositoryInterface
     */
    public UserPromotionRepositoryInterface $repository;

    /**
     * UserPromotionAdminController constructor.
     *
     * @param UserPromotionRepositoryInterface $repository
     */
    public function __construct(UserPromotionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse promotion.
     *
     * @param IndexRequest $request
     *
     * @return ItemCollection<UserPromotionItem>
     * @group user/promotion
     * @authenticated
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->get($params);

        return new ItemCollection($data);
    }

    /**
     * Create promotion.
     *
     * @param StoreRequest $request
     *
     * @return Detail
     * @throws ValidatorException
     * @group user/promotion
     * @authenticated
     */
    public function store(StoreRequest $request): Detail
    {
        $params = $request->validated();
        $data   = $this->repository->create($params);

        return new Detail($data);
    }

    /**
     * View promotion item.
     *
     * @param int $id
     *
     * @return Detail
     * @group user/promotion
     * @authenticated
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->find($id);

        return new Detail($data);
    }

    /**
     * Update promotion item.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return Detail
     * @throws ValidatorException
     * @group user/promotion
     * @authenticated
     */
    public function update(UpdateRequest $request, int $id): Detail
    {
        $params = $request->validated();
        $data   = $this->repository->update($params, $id);

        return new Detail($data);
    }

    /**
     * Delete promotion item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @group user/promotion
     * @authenticated
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->success([
            'id' => $id,
        ]);
    }

    /**
     * View editing form.
     *
     * @param int $id
     *
     * @return AbstractForm
     * @group user/promotion
     * @authenticated
     */
    public function editForm(int $id): AbstractForm
    {
        return new EditForm($this->repository->find($id));
    }

    /**
     * View creation form.
     *
     * @return AbstractForm
     * @group user/promotion
     * @authenticated
     */
    public function createForm(): AbstractForm
    {
        return new CreateForm(new UserPromotion());
    }
}
