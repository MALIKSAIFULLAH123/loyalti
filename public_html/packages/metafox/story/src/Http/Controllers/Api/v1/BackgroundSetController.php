<?php

namespace MetaFox\Story\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Story\Http\Requests\v1\BackgroundSet\IndexRequest;
use MetaFox\Story\Http\Resources\v1\BackgroundSet\BackgroundSetDetail as Detail;
use MetaFox\Story\Http\Resources\v1\BackgroundSet\BackgroundSetItemCollection as ItemCollection;
use MetaFox\Story\Repositories\BackgroundSetRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Story\Http\Controllers\Api\BackgroundSetController::$controllers;.
 */

/**
 * Class BackgroundSetController.
 * @codeCoverageIgnore
 * @ignore
 */
class BackgroundSetController extends ApiController
{
    /**
     * @var BackgroundSetRepositoryInterface
     */
    private BackgroundSetRepositoryInterface $repository;

    /**
     * BackgroundSetController Constructor.
     *
     * @param BackgroundSetRepositoryInterface $repository
     */
    public function __construct(BackgroundSetRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     * @return mixed
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data = $this->repository->viewBackgroundSetForFE(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * View item.
     *
     * @param int $id
     *
     * @return Detail
     * @throws AuthenticationException
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->viewBackgroundSet(user(), $id);

        return new Detail($data);
    }
}
