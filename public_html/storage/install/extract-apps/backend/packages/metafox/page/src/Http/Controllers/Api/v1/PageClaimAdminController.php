<?php

namespace MetaFox\Page\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Page\Http\Requests\v1\PageClaim\Admin\IndexRequest;
use MetaFox\Page\Http\Requests\v1\PageClaim\Admin\UpdateRequest;
use MetaFox\Page\Http\Resources\v1\PageClaim\Admin\PageClaimItemCollection as ItemCollection;
use MetaFox\Page\Repositories\PageClaimRepositoryInterface;
use MetaFox\Page\Support\PageClaimSupport;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Page\Http\Controllers\Api\PageClaimAdminController::$controllers;.
 */

/**
 * Class PageClaimAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class PageClaimAdminController extends ApiController
{
    /**
     * @var PageClaimRepositoryInterface
     */
    private PageClaimRepositoryInterface $repository;

    /**
     * PageClaimAdminController Constructor.
     *
     * @param PageClaimRepositoryInterface $repository
     */
    public function __construct(PageClaimRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest           $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->viewAdminPageClaims($params['limit'] ?? 100);

        return new ItemCollection($data);
    }

    /**
     * Update item.
     *
     * @param  UpdateRequest          $request
     * @param  int                    $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $status = $params['status'];
        $this->repository->updatePageClaim($id, $status);

        $message = match ($status) {
            PageClaimSupport::STATUS_PENDING => __p('page::phrase.your_claim_request_sent_successfully'),
            PageClaimSupport::STATUS_CANCEL  => __p('page::phrase.cancel_successfully'),
            PageClaimSupport::STATUS_DENY    => __p('page::phrase.denied_successfully'),
            default                          => __p('page::phrase.approved_successfully')
        };

        return $this->success([], [], $message);
    }
}
