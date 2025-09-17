<?php

namespace MetaFox\Featured\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Featured\Policies\InvoicePolicy;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Featured\Http\Resources\v1\Invoice\Admin\InvoiceItemCollection as ItemCollection;
use MetaFox\Featured\Http\Resources\v1\Invoice\Admin\InvoiceDetail as Detail;
use MetaFox\Featured\Repositories\InvoiceRepositoryInterface;
use MetaFox\Featured\Http\Requests\v1\Invoice\Admin\IndexRequest;
use MetaFox\Featured\Http\Requests\v1\Invoice\Admin\StoreRequest;
use MetaFox\Featured\Http\Requests\v1\Invoice\Admin\UpdateRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Featured\Http\Controllers\Api\InvoiceAdminController::$controllers;
 */

/**
 * Class InvoiceAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class InvoiceAdminController extends ApiController
{
    /**
     * @var InvoiceRepositoryInterface
     */
    private InvoiceRepositoryInterface $repository;

    /**
     * InvoiceAdminController Constructor
     *
     * @param  InvoiceRepositoryInterface $repository
     */
    public function __construct(InvoiceRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item
     *
     * @param  IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->viewInvoicesAdminCP($context, $params);

        return new ItemCollection($data);
    }

    /**
     * @param int $id
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function cancel(int $id): JsonResponse
    {
        $invoice = $this->repository->find($id);

        $context = user();

        policy_authorize(InvoicePolicy::class, 'cancel', $context, $invoice);

        $this->repository->cancelInvoice($context, $invoice);

        $invoice->refresh();

        return $this->success(new Detail($invoice), [], __p('featured::phrase.invoice_was_cancelled_successfully'));
    }

    /**
     * @param int $id
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function markAsPaid(int $id): JsonResponse
    {
        $invoice = $this->repository->find($id);

        $context = user();

        policy_authorize(InvoicePolicy::class, 'markAsPaid', $context, $invoice);

        $this->repository->markAsPaid($context, $invoice);

        $invoice->refresh();

        return $this->success(new Detail($invoice), [], __p('featured::admin.invoice_was_marked_as_paid_successfully'));
    }
}
