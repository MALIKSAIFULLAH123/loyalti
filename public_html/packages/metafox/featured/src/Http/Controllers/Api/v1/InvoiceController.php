<?php

namespace MetaFox\Featured\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Featured\Http\Requests\v1\Invoice\IndexRequest;
use MetaFox\Featured\Http\Requests\v1\Invoice\PaymentRequest;
use MetaFox\Featured\Http\Resources\v1\Invoice\InvoiceDetail;
use MetaFox\Featured\Http\Resources\v1\Invoice\InvoiceItemCollection;
use MetaFox\Featured\Http\Resources\v1\Invoice\InvoiceItemCollection as ItemCollection;
use MetaFox\Featured\Models\Invoice;
use MetaFox\Featured\Models\Package;
use MetaFox\Featured\Policies\InvoicePolicy;
use MetaFox\Featured\Repositories\InvoiceRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Payment\Repositories\GatewayRepositoryInterface;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Payment\Traits\Controller\HandleExtraPaymentParamsTrait;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\SEO\ActionMeta;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Featured\Http\Controllers\Api\InvoiceController::$controllers;
 */

/**
 * Class InvoiceController
 * @codeCoverageIgnore
 * @ignore
 */
class InvoiceController extends ApiController
{
    use HandleExtraPaymentParamsTrait;

    /**
     * @var InvoiceRepositoryInterface
     */
    private InvoiceRepositoryInterface $repository;

    /**
     * InvoiceController Constructor
     *
     * @param InvoiceRepositoryInterface $repository
     */
    public function __construct(InvoiceRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item
     *
     * @param IndexRequest $request
     *
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $context = user();

        if ($context->isGuest()) {
            throw new AuthorizationException();
        }

        $data = $this->repository->viewInvoices($context, $params);

        return new InvoiceItemCollection($data);
    }

    /**
     * @param PaymentRequest $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function payment(PaymentRequest $request): JsonResponse
    {
        $params = $request->validated();

        $id = Arr::get($params, 'invoice_id');

        $invoice = $this->repository->find($id);

        $context = user();

        policy_authorize(InvoicePolicy::class, 'prepayment', $context, $invoice);

        if (policy_check(InvoicePolicy::class, 'cancelOutdatedInvoicesWithoutRefreshing', $invoice)) {
            $this->repository->cancelOutdatedInvoices($invoice);

            return $this->success([], [], __p('featured::phrase.outdated_invoices_were_cancelled_successfully'));
        }

        $nextPaymentForm = $this->getNextPaymentForm($context, 'featured/invoice/payment', $params);

        if ($nextPaymentForm instanceof AbstractForm) {
            return $this->success($nextPaymentForm, $nextPaymentForm->getMultiStepFormMeta());
        }

        $data = $this->repository->payment($invoice, Arr::get($params, 'payment_gateway'), $this->getExtraPaymentParams($context, $params));

        if (!is_array($data)) {
            throw new AuthorizationException();
        }

        $status = Arr::get($data, 'status', false);

        if (false === $status) {
            throw new HttpException(403, Arr::get($data, 'message', __p('featured::validation.can_not_pay_this_invoice')));
        }

        return $this->success([
            'url'   => Arr::get($data, 'gateway_redirect_url'),
            'token' => Arr::get($data, 'gateway_token'),
        ], $this->getMetaData(), Arr::get($data, 'message'));
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function cancel(int $id): JsonResponse
    {
        $context = user();

        $invoice = $this->repository->find($id);

        policy_authorize(InvoicePolicy::class, 'cancel', $context, $invoice);

        $this->repository->cancelInvoice($context, $invoice);

        return $this->success(new InvoiceDetail($invoice->refresh()), [], __p('featured::phrase.invoice_was_cancelled_successfully'));
    }

    public function show(int $id): JsonResponse
    {
        $invoice = $this->repository->find($id);

        $context = user();

        policy_authorize(InvoicePolicy::class, 'view', $context, $invoice);

        return $this->success(new InvoiceDetail($invoice));
    }

    protected function getNextPaymentForm($context, string $actionUrl, array $params): ?AbstractForm
    {
        /**
         * @var Invoice $invoice
         */
        $invoice = $this->repository->find(Arr::get($params, 'invoice_id'));

        $package = $invoice->package;

        $price = $invoice->price;

        $currency = $invoice->currency;

        $title = null;

        if ($package instanceof Package) {
            $title = __p('featured::phrase.featured_package_title', ['title' => $package->title]);
        }

        return Payment::getNextPaymentForm($context, Arr::get($params, 'payment_gateway'), $params, [
            'price'       => $price,
            'currency_id' => $currency,
            'action_url'  => $actionUrl,
            'order_detail_info'  => [
                [
                    'title'     => $title,
                    'link'      => $invoice->toLink(),
                    'quantity'  => 1,
                    'price'     => $price,
                    'currency'  => $currency,
                    'sub_total' => 0,
                    'entity_type' => $package?->entityType(),
                    'entity_id'   => $package?->entityId(),
                ]
            ]
        ]);
    }

    protected function getMetaData(): array
    {
        if (MetaFox::isMobile()) {
            return [];
        }

        $actionMeta = new ActionMeta();

        $actionMeta->continueAction()->type(MetaFoxConstant::TYPE_MULTISTEP_FORM_DONE);

        return $actionMeta->toArray();
    }
}
