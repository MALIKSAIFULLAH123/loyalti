<?php

namespace MetaFox\Page\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Page\Policies\PageClaimPolicy;
use MetaFox\Page\Support\PageClaimSupport;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Page\Http\Resources\v1\PageClaim\PageClaimItemCollection as ItemCollection;
use MetaFox\Page\Http\Resources\v1\PageClaim\PageClaimDetail as Detail;
use MetaFox\Page\Repositories\PageClaimRepositoryInterface;
use MetaFox\Page\Http\Requests\v1\PageClaim\IndexRequest;
use MetaFox\Page\Http\Requests\v1\PageClaim\StoreRequest;
use MetaFox\Page\Http\Requests\v1\PageClaim\UpdateRequest;
use MetaFox\Platform\MetaFoxConstant;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Page\Http\Controllers\Api\PageClaimController::$controllers;
 */

/**
 * Class PageClaimController.
 * @codeCoverageIgnore
 * @ignore
 */
class PageClaimController extends ApiController
{
    /**
     * @var PageClaimRepositoryInterface
     */
    private PageClaimRepositoryInterface $repository;

    /**
     * PageClaimController Constructor.
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
     * @param  IndexRequest            $request
     * @return mixed
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params  = $request->validated();
        $context = $owner = user();

        policy_authorize(PageClaimPolicy::class, 'view', $context, $owner);

        $data   = $this->repository->viewPageClaims($context, $owner, $params);

        return new ItemCollection($data);
    }

    /**
     * @param  int                     $id
     * @return Detail
     * @throws AuthenticationException
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->viewPageClaim(user(), $id);

        return new Detail($data);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $this->repository->createPageClaim(user(), $params['page_id'], $params['message']);

        return $this->success([], [], __p('page::phrase.your_claim_request_sent_successfully'));
    }

    /**
     * Update item.
     *
     * @param  UpdateRequest           $request
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params    = $request->validated();
        $status    = Arr::get($params, 'status');
        $pageClaim = $this->repository->find($id);

        policy_authorize(PageClaimPolicy::class, 'update', user(), $pageClaim);

        if (Arr::has($params, 'message')) {
            $pageClaim->update(['message' => $params['message']]);

            return $this->success(new Detail($pageClaim->refresh()), [], __p('page::phrase.updated_successfully'));
        }

        if (!Arr::has($params, 'status')) {
            throw new AuthorizationException(__p('page::phrase.cannot_update_the_claim_page'));
        }

        $this->repository->updatePageClaim($id, $status);

        $message = match ((int) $status) {
            PageClaimSupport::STATUS_PENDING => __p('page::phrase.your_claim_request_sent_successfully'),
            PageClaimSupport::STATUS_CANCEL  => __p('page::phrase.cancel_successfully'),
            PageClaimSupport::STATUS_DENY    => __p('page::phrase.denied_successfully'),
            default                          => __p('page::phrase.approved_successfully')
        };

        return $this->success(new Detail($pageClaim->refresh()), [], $message);
    }

    /**
     * @throws AuthenticationException
     */
    public function resubmit(int $id): JsonResponse
    {
        $pageClaim = $this->repository->resubmitPageClaim(user(), $id);

        return $this->success(new Detail($pageClaim), [], __p('page::phrase.resubmit_successfully'));
    }
}
