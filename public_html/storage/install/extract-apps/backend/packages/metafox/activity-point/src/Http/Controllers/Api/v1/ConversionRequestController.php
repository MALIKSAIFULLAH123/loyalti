<?php

namespace MetaFox\ActivityPoint\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Http\Requests\v1\ConversionRequest\IndexRequest;
use MetaFox\ActivityPoint\Http\Requests\v1\ConversionRequest\StoreRequest;
use MetaFox\ActivityPoint\Http\Resources\v1\ConversionRequest\ConversionRequestDetail as Detail;
use MetaFox\ActivityPoint\Http\Resources\v1\ConversionRequest\ConversionRequestItemCollection as ItemCollection;
use MetaFox\ActivityPoint\Models\ConversionRequest;
use MetaFox\ActivityPoint\Policies\ConversionRequestPolicy;
use MetaFox\ActivityPoint\Repositories\ConversionRequestRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\SEO\ActionMeta;
use MetaFox\SEO\PayloadActionMeta;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\ActivityPoint\Http\Controllers\Api\ConversionRequestController::$controllers;
 */

/**
 * Class ConversionRequestController
 *
 * @codeCoverageIgnore
 * @ignore
 */
class ConversionRequestController extends ApiController
{
    /**
     * @var ConversionRequestRepositoryInterface
     */
    private ConversionRequestRepositoryInterface $repository;

    /**
     * ConversionRequestController Constructor
     *
     * @param ConversionRequestRepositoryInterface $repository
     */
    public function __construct(ConversionRequestRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item
     *
     * @param IndexRequest $request
     *
     * @return mixed
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): JsonResource
    {
        $params = $request->validated();

        $context = user();

        if ($context->isGuest()) {
            throw new AuthorizationException();
        }

        if (Arr::has($params, 'id')) {
            $request = $this->repository->find(Arr::get($params, 'id'));

            if (!$request instanceof ConversionRequest || $request->userId() != $context->entityId()) {
                throw new AuthorizationException();
            }
        }

        $data = $this->repository->viewConversionRequests($context, $params);

        return new ItemCollection($data);
    }

    /**
     * Store item
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        policy_authorize(ConversionRequestPolicy::class, 'createConversionRequest', $context, Arr::get($params, 'points', 0));

        $currency = app('currency')->getDefaultCurrencyId();
        $data     = $this->repository->createConversionRequest($context, Arr::get($params, 'points'), $currency);

        $redirectUrl = 'activitypoint/conversion-request';
        $actionMeta  = new ActionMeta();
        $actionMeta->nextAction()
            ->type('navigate')
            ->payload(PayloadActionMeta::payload()->url($redirectUrl));

        return $this->success(new Detail($data), $actionMeta->toArray(), __p('activitypoint::phrase.request_was_created_successfully'));
    }

    public function cancel(int $id): JsonResponse
    {
        $request = $this->repository->find($id);

        $context = user();

        policy_authorize(ConversionRequestPolicy::class, 'cancelConversionRequest', $context, $request);

        $request = $this->repository->cancelConversionRequest($context, $request);

        return $this->success(new Detail($request), [], __p('activitypoint::phrase.request_was_cancelled_successfully'));
    }

    public function show(int $id): JsonResponse
    {
        $request = $this->repository->find($id);

        $context = user();

        policy_authorize(ConversionRequestPolicy::class, 'view', $context, $request);

        return $this->success(new Detail($request));
    }
}
