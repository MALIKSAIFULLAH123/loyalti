<?php

namespace MetaFox\Search\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Search\Http\Resources\v1\Reindex\Admin\ReindexForm;
use MetaFox\Search\Repositories\SearchRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Search\Http\Controllers\Api\SearchController::$controllers.
 */

/**
 * Class ReindexAdminController.
 * @ingore
 * @codeCoverageIgnore
 * @group search
 */
class ReindexAdminController extends ApiController
{
    /**
     * @var SearchRepositoryInterface
     */
    public $repository;

    public function __construct(SearchRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function create()
    {
        return $this->success(new ReindexForm());
    }

    public function store(Request $request): JsonResponse
    {
        $this->repository->reindex();

        return $this->success([], [], __p('search::phrase.the_reindexing_process_has_been_started'));
    }
}
