<?php

namespace MetaFox\Authorization\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Authorization\Http\Requests\v1\Permission\IndexRequest;
use MetaFox\Authorization\Http\Resources\v1\Permission\PermissionDetail as Detail;
use MetaFox\Authorization\Http\Resources\v1\Permission\PermissionItemCollection as ItemCollection;
use MetaFox\Authorization\Repositories\Contracts\PermissionRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * --------------------------------------------------------------------------
 *  Api Controller
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\User\Http\Controllers\Api\PermissionController::$controllers;
 */

/**
 * Class PermissionController.
 * @ignore
 * @codeCoverageIgnore
 * @group user
 * @authenticated
 * @admincp
 */
class PermissionController extends ApiController
{
    /**
     * @var PermissionRepositoryInterface
     */
    public PermissionRepositoryInterface $repository;

    public function __construct(PermissionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param IndexRequest $request
     *
     * @return JsonResource
     * @throws AuthenticationException
     * @group authorization/permission
     */
    public function index(IndexRequest $request): JsonResource
    {
        $params = $request->validated();
        $data   = $this->repository->viewPermissions(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * @param int $id
     *
     * @return Detail
     * @throws AuthenticationException
     * @group authorization/permission
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->viewPermission(user(), $id);

        return new Detail($data);
    }
}
