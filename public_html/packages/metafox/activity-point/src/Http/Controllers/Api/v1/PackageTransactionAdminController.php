<?php

namespace MetaFox\ActivityPoint\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\ActivityPoint\Http\Requests\v1\PackageTransaction\Admin\IndexRequest;
use MetaFox\ActivityPoint\Http\Resources\v1\PackageTransaction\Admin\PackageTransactionItemCollection as ItemCollection;
use MetaFox\ActivityPoint\Repositories\PurchasePackageRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\ActivityPoint\Http\Controllers\Api\PackageTransactionAdminController::$controllers;.
 */

/**
 * Class PackageTransactionAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class PackageTransactionAdminController extends ApiController
{
    /**
     * @var PurchasePackageRepositoryInterface
     */
    private PurchasePackageRepositoryInterface $repository;

    /**
     * PurchasePackageRepositoryInterface Constructor.
     *
     * @param PurchasePackageRepositoryInterface $repository
     */
    public function __construct(PurchasePackageRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest            $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ResourceCollection
    {
        $context = user();

        $params = $request->validated();

        $data = $this->repository->viewPurchasePackageAdminCP($context, $params);

        return new ItemCollection($data);
    }
}
