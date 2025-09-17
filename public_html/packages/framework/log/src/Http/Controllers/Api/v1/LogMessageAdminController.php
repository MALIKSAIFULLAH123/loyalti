<?php

namespace MetaFox\Log\Http\Controllers\Api\v1;

use MetaFox\Log\Http\Requests\v1\LogMessage\Admin\IndexRequest;
use MetaFox\Log\Http\Resources\v1\LogMessage\Admin\LogMessageItemCollection as ItemCollection;
use MetaFox\Log\Models\LogMessage;
use MetaFox\Log\Repositories\LogMessageRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Log\Http\Controllers\Api\LogMessageAdminController::$controllers.
 */

/**
 * Class LogMessageAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class LogMessageAdminController extends ApiController
{
    /**
     * @var LogMessageRepositoryInterface
     */
    private LogMessageRepositoryInterface $repository;

    /**
     * LogMessageAdminController Constructor.
     *
     * @param LogMessageRepositoryInterface $repository
     */
    public function __construct(LogMessageRepositoryInterface $repository)
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

        $table = 'log_messages';

        $data = (new LogMessage())->setTable($table)->newQuery()
            ->orderBy('timestamp', 'desc')
            ->forPage($params['page'] ?? 1)
            ->paginate($params['limit'] ?? 50);

        return new ItemCollection($data);
    }
}
