<?php

namespace MetaFox\Core\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MetaFox\Layout\Jobs\CheckBuild;
use MetaFox\Layout\Repositories\BuildRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * Class BuildController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 * @group core
 */
class BuildController extends ApiController
{
    /**
     * @param  Request      $request
     * @return JsonResponse
     */
    public function buildCallback(Request $request): JsonResponse
    {
        $params = $request->validate([
            'buildId' => 'string|required',
        ]);

        $respository = resolve(BuildRepositoryInterface::class);

        $task = $respository->getByBuildId($params['buildId']);

        $last = $respository->findLast();

        if ($last->id !== $task->id || $task->expired() || !$task->running()) {
            return $this->error('This build is not available.');
        }

        CheckBuild::dispatchSync();

        return $this->success(['received' => true]);
    }
}
