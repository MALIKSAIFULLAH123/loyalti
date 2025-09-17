<?php

namespace MetaFox\Activity\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Activity\Http\Resources\v1\ActivityHistory\ActivityHistoryItemCollection as ItemCollection;
use MetaFox\Activity\Repositories\ActivityHistoryRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Activity\Http\Controllers\Api\ActivityHistoryController::$controllers;.
 */

/**
 * Class ActivityHistoryController.
 * @codeCoverageIgnore
 * @ignore
 */
class ActivityHistoryController extends ApiController
{
    /**
     * @var ActivityHistoryRepositoryInterface
     */
    private ActivityHistoryRepositoryInterface $repository;

    /**
     * ActivityHistoryController Constructor.
     *
     * @param ActivityHistoryRepositoryInterface $repository
     */
    public function __construct(ActivityHistoryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  int                     $id
     * @return ItemCollection
     * @throws AuthenticationException
     */
    public function index(int $id)
    {
        $content = user();

        $data = $this->repository->viewHistories($content, $id);

        return new ItemCollection($data);
    }
}
