<?php

namespace MetaFox\Report\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Report\Http\Requests\v1\ReportReason\IndexRequest;
use MetaFox\Report\Http\Resources\v1\ReportReason\ReportReasonDetail as Detail;
use MetaFox\Report\Http\Resources\v1\ReportReason\ReportReasonItemCollection as ItemCollection;
use MetaFox\Report\Repositories\ReportReasonRepositoryInterface;

/**
 * Class ReportReasonController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group report
 * @admincp
 */
class ReportReasonController extends ApiController
{
    /**
     * @var ReportReasonRepositoryInterface
     */
    private ReportReasonRepositoryInterface $repository;

    /**
     * @param ReportReasonRepositoryInterface $repository
     */
    public function __construct(ReportReasonRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse report reasons.
     *
     * @param IndexRequest $request
     *
     * @return JsonResource
     * @throws AuthorizationException|AuthenticationException
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();
        $data   = $this->repository->viewReasons(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Display a report reason.
     *
     * @param int $id
     *
     * @return Detail
     * @throws AuthorizationException|AuthenticationException
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->viewReason(user(), $id);

        return new Detail($data);
    }
}
