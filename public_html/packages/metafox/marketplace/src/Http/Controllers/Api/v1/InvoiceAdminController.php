<?php

namespace MetaFox\Marketplace\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Marketplace\Http\Requests\v1\Invoice\Admin\IndexRequest;
use MetaFox\Marketplace\Http\Resources\v1\Invoice\Admin\InvoiceItem;
use MetaFox\Marketplace\Http\Resources\v1\Invoice\Admin\InvoiceItemCollection as ItemCollection;
use MetaFox\Marketplace\Http\Resources\v1\Invoice\InvoiceSimpleDetail;
use MetaFox\Marketplace\Http\Resources\v1\InvoiceTransaction\TransactionItemCollection;
use MetaFox\Marketplace\Repositories\InvoiceAdminRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Marketplace\Http\Controllers\Api\InvoiceAdminController::$controllers;
 */

/**
 * Class InvoiceAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class InvoiceAdminController extends ApiController
{
    /**
     * InvoiceAdminController Constructor
     *
     */
    public function __construct(protected InvoiceAdminRepositoryInterface $repository)
    {
    }

    /**
     * Browse item
     *
     * @param IndexRequest $request
     * @return mixed
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->viewInvoices(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Delete item
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteInvoice(user(), $id);
        return $this->success([], [], __p('marketplace::phrase.invoice_successfully_deleted'));
    }

    public function cancel(int $id): JsonResponse
    {
        $context = user();

        $invoice = $this->repository->cancelInvoice($context, $id);

        return $this->success(new InvoiceItem($invoice), [], __p('marketplace::phrase.invoice_successfully_cancelled'));
    }

    /**
     * @param int $id
     * @return TransactionItemCollection
     * @throws AuthenticationException
     */
    public function viewTransactions(int $id): TransactionItemCollection
    {
        $context = user();

        $collection = $this->repository->viewTransactionsInAdminCP($context, $id);

        return new TransactionItemCollection($collection);
    }

    /**
     * @param int $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function viewShortTransactions(int $id): JsonResponse
    {
        $context = user();

        $data = $this->repository->viewInvoice($context, $id);

        return $this->success(new InvoiceSimpleDetail($data));
    }
}
