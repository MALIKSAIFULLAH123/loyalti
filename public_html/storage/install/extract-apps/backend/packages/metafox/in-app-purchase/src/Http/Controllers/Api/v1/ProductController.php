<?php

namespace MetaFox\InAppPurchase\Http\Controllers\Api\v1;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MetaFox\InAppPurchase\Http\Requests\v1\Product\ReceiptRequest;
use MetaFox\InAppPurchase\Http\Resources\v1\Product\Admin\UpdateProductForm;
use MetaFox\InAppPurchase\Support\Facades\InAppPur;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\InAppPurchase\Http\Resources\v1\Product\ProductItemCollection as ItemCollection;
use MetaFox\InAppPurchase\Http\Resources\v1\Product\ProductDetail as Detail;
use MetaFox\InAppPurchase\Repositories\ProductRepositoryInterface;
use MetaFox\InAppPurchase\Http\Requests\v1\Product\IndexRequest;
use MetaFox\InAppPurchase\Http\Requests\v1\Product\StoreRequest;
use MetaFox\InAppPurchase\Http\Requests\v1\Product\UpdateRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\InAppPurchase\Http\Controllers\Api\ProductController::$controllers;
 */

/**
 * Class ProductController.
 * @codeCoverageIgnore
 * @ignore
 */
class ProductController extends ApiController
{
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $repository;

    /**
     * ProductController Constructor.
     *
     * @param ProductRepositoryInterface $repository
     */
    public function __construct(ProductRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->viewProducts($params);

        return new ItemCollection($data);
    }

    /**
     * Store item.
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
     * @param  UpdateRequest $request
     * @param  int           $id
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params    = $request->validated();

        $data = $this->repository->updateProduct($id, $params);

        return $this->success(new Detail($data), [], __p('in-app-purchase::admin.product_successfully_updated'));
    }

    /**
     * Delete item.
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
     * @throws Exception
     */
    public function callback(string $platform, Request $request): JsonResponse
    {
        $callbackData = $request->getContent() ?? $request->all();
        if (!is_array($callbackData)) {
            $callbackData = json_decode($callbackData, true);
        }
        if (!$callbackData) {
            return $this->error();
        }

        InAppPur::handleCallback($platform, $callbackData);

        return $this->success();
    }

    public function validateReceipt(ReceiptRequest $request): JsonResponse
    {
        $params    = $request->validated();
        $context   = user();

        try {
            if (!InAppPur::verifyReceipt($params, $context)) {
                return $this->error();
            }
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            if ($errorMessage == 'E_ITEM_DUPLICATED') {
                return $this->success(['duplicated' => true]);
            }

            return $this->error($e->getMessage() ?? __p('in-app-purchase::phrase.cannot_verify_purchase'));
        }

        return $this->success();
    }

    public function edit(int $id): JsonResponse
    {
        $data = $this->repository->find($id);

        return $this->success(new UpdateProductForm($data));
    }

    public function getProductByItem(string $itemType, int $itemId): JsonResponse
    {
        $data = $this->repository->getProductByItem($itemId, $itemType);
        if (null == $data) {
            return $this->error(
                __p('core::phrase.the_entity_name_you_are_looking_for_can_not_be_found', ['entity_name' => 'product']),
                403
            );
        }

        return $this->success(new Detail($data));
    }
}
