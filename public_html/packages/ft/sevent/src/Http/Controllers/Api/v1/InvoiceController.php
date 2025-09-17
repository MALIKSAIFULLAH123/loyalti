<?php

namespace Foxexpert\Sevent\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Foxexpert\Sevent\Http\Resources\v1\Invoice\InvoiceItemCollection as ItemCollection;
use Foxexpert\Sevent\Http\Resources\v1\Invoice\InvoiceDetail as Detail;
use Foxexpert\Sevent\Repositories\InvoiceRepositoryInterface;
use Foxexpert\Sevent\Http\Requests\v1\Invoice\IndexRequest;
use Foxexpert\Sevent\Http\Requests\v1\Invoice\StoreRequest;
use Foxexpert\Sevent\Http\Requests\v1\Invoice\UpdateRequest;
use Prettus\Validator\Exceptions\ValidatorException;
use Metafox\User\Models\User;
/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \Foxexpert\Sevent\Http\Controllers\Api\InvoiceController::$controllers;
 */

/**
 * Class InvoiceController.
 * @codeCoverageIgnore
 * @ignore
 */
class InvoiceController extends ApiController
{
    /**
     * @var InvoiceRepositoryInterface
     */
    private InvoiceRepositoryInterface $repository;

    /**
     * InvoiceController Constructor.
     *
     * @param InvoiceRepositoryInterface $repository
     */
    public function __construct(InvoiceRepositoryInterface $repository)
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

        $context = user();

        $data   = $this->repository->viewInvoices($context, $params);

        return new ItemCollection($data);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return Detail
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $listingId = Arr::get($params, 'id', 0);
        $gatewayId = Arr::get($params, 'payment_gateway', 0);

        $data = $this->repository->createInvoice($context, $listingId, $gatewayId);

        $status = Arr::get($data, 'status', false);

        if (false === $status) {
            return $this->error(__p('sevent::phrase.can_not_create_order_for_listing_purchasement'));
        }

        return $this->success([
            'url'   => Arr::get($data, 'gateway_redirect_url'),
            'token' => Arr::get($data, 'gateway_token'),
        ]);
    }

    /**
     * View item.
     *
     * @param int $id
     *
     * @return Detail
     */
    public function show(int $id): Detail
    {
        $context = user();

        $data = $this->repository->viewInvoice($context, $id);

        return new Detail($data);
    }

    /**
     * Update item.
     *
     * @param  UpdateRequest      $request
     * @param  int                $id
     * @return Detail|null
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): ?Detail
    {
        return null;
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse|null
     */
    public function destroy(int $id): ?JsonResponse
    {
        return null;
    }
}
