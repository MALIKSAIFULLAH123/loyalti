<?php

namespace MetaFox\Video\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Video\Http\Requests\v1\VerifyProcess\Admin\IndexRequest;
use MetaFox\Video\Http\Resources\v1\VerifyProcess\Admin\VerifyProcessItemCollection as ItemCollection;
use MetaFox\Video\Repositories\VerifyProcessRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Video\Http\Controllers\Api\VerifyProcessAdminController::$controllers;
 */

/**
 * Class VerifyProcessAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class VerifyProcessAdminController extends ApiController
{
    /**
     * VerifyProcessAdminController Constructor
     *
     */
    public function __construct(protected VerifyProcessRepositoryInterface $repository) {}

    /**
     * Browse item
     *
     * @param IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->viewProcesses(user(), $params)
            ->paginate($params['limit'] ?? 100, ['video_verify_processes.*']);

        return new ItemCollection($data);
    }

    public function stop(int $id): JsonResponse
    {
        $model = $this->repository->find($id);

        $this->repository->stopProcess($model);

        return $this->success([], [], __p('video::phrase.was_stopped_successfully'));
    }

    public function process(int $id): JsonResponse
    {
        if ($this->repository->checkProcessExist()) {
            abort(403, __p('video::phrase.mass_verification_video_existence_already_running'));
        }

        $model = $this->repository->find($id);

        $this->repository->process($model);

        return $this->success([], [], __p('video::phrase.was_processed_successfully'));
    }
}
