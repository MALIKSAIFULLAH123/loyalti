<?php

namespace MetaFox\Mfa\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Mfa\Http\Resources\v1\Service\ServiceItemCollection as ItemCollection;
use MetaFox\Mfa\Http\Resources\v1\Service\ServiceDetail as Detail;
use MetaFox\Mfa\Repositories\ServiceRepositoryInterface;
use MetaFox\Mfa\Http\Requests\v1\Service\IndexRequest;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Mfa\Http\Controllers\Api\ServiceController::$controllers;
 */

/**
 * Class ServiceController.
 * @codeCoverageIgnore
 * @ignore
 */
class ServiceController extends ApiController
{
    /**
     * @var ServiceRepositoryInterface
     */
    private ServiceRepositoryInterface $repository;

    /**
     * ServiceController Constructor.
     *
     * @param ServiceRepositoryInterface $repository
     */
    public function __construct(ServiceRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest       $request
     * @return ItemCollection
     * @throws ValidatorException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $data    = $this->repository->viewServices(user());

        return new ItemCollection($data);
    }
}
