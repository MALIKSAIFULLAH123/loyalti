<?php

namespace MetaFox\Advertise\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Advertise\Contracts\AdvertisePaymentInterface;
use MetaFox\Advertise\Http\Requests\v1\Invoice\IndexRequest;
use MetaFox\Advertise\Http\Requests\v1\Invoice\PaymentRequest;
use MetaFox\Advertise\Http\Resources\v1\Invoice\InvoiceItem;
use MetaFox\Advertise\Http\Resources\v1\Invoice\InvoiceItemCollection as ItemCollection;
use MetaFox\Advertise\Models\Invoice;
use MetaFox\Advertise\Repositories\InvoiceRepositoryInterface;
use MetaFox\Advertise\Support\Facades\Support;
use MetaFox\Form\AbstractForm;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Payment\Traits\Controller\HandleExtraPaymentParamsTrait;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\SEO\ActionMeta;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Advertise\Http\Controllers\Api\InvoiceController::$controllers;
 */

/**
 * Class InvoiceController.
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
     * @param IndexRequest $request
     *
     * @return ItemCollection
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->viewInvoices($context, $params);

        return new ItemCollection($data);
    }

    public function payment(PaymentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $invoiceId = Arr::get($data, 'invoice_id');

        $itemType = Arr::get($data, 'item_type');

        $itemId = Arr::get($data, 'item_id');

        $gatewayId = Arr::get($data, 'payment_gateway');

        $context           = user();

        $nextPaymentGatewayForm = $this->getNextPaymentForm($context, 'advertise/invoice/payment', $data);

        if ($nextPaymentGatewayForm instanceof AbstractForm) {
            return $this->success($nextPaymentGatewayForm, $nextPaymentGatewayForm->getMultiStepFormMeta());
        }

        $result = $this->repository->paymentInvoice($context, $itemId, $itemType, $gatewayId, $invoiceId, $this->getExtraPaymentParams($context, $data));

        $status = Arr::get($result, 'status', false);

        if (false === $status) {
            abort(403, __p('advertise::phrase.can_not_pay_this_invoice'));
        }

        return $this->success([
            'url'   => Arr::get($result, 'gateway_redirect_url'),
            'token' => Arr::get($result, 'gateway_token'),
        ], $this->getMetaData(), Arr::get($result, 'message'));
    }

    public function change(PaymentRequest $request): JsonResponse
    {
        $data = $request->validated();
        $context           = user();
        $itemId            = Arr::get($data, 'item_id');
        $itemType          = Arr::get($data, 'item_type');
        $gatewayId         = Arr::get($data, 'payment_gateway');
        $nextPaymentForm = $this->getNextPaymentForm($context, 'advertise/invoice/change', $data);

        if ($nextPaymentForm instanceof AbstractForm) {
            return $this->success($nextPaymentForm, $nextPaymentForm->getMultiStepFormMeta());
        }

        $result = $this->repository->paymentInvoice($context, $itemId, $itemType, $gatewayId, null, $this->getExtraPaymentParams($context, $data));

        $status = Arr::get($result, 'status', false);

        if (false === $status) {
            abort(403, __p('advertise::phrase.can_not_pay_this_invoice'));
        }

        return $this->success([
            'url'   => Arr::get($result, 'gateway_redirect_url'),
            'token' => Arr::get($result, 'gateway_token'),
        ], $this->getMetaData(), Arr::get($result, 'message'));
    }

    public function cancel(int $id): JsonResponse
    {
        $context = user();

        $invoice = $this->repository->cancelInvoice($context, $id);

        return $this->success(new InvoiceItem($invoice), [], __p('advertise::phrase.invoice_successfully_cancelled'));
    }

    protected function getNextPaymentForm($context, string $actionUrl, array $params): ?AbstractForm
    {
        $id = Arr::get($params, 'invoice_id');

        if (null === $id) {
            $data = $this->repository->getRefreshInfo($context, Arr::get($params, 'item_id'), Arr::get($params, 'item_type'));

            if (null === $data) {
                return null;
            }

            $price = Arr::get($data, 'price');

            $currency = Arr::get($data, 'currency_id');

            $item = Support::getMorphedModel(Arr::get($params, 'item_id'), Arr::get($params, 'item_type'));
        } else {
            /**
             * @var Invoice $invoice
             */
            $invoice = $this->repository->find($id);

            $price = $invoice->price;

            $currency = $invoice->currency_id;

            $item = $invoice->item;
        }

        $title = null;

        if ($item instanceof AdvertisePaymentInterface) {
            $title = $item->payment_order_title;
        }

        return Payment::getNextPaymentForm($context, Arr::get($params, 'payment_gateway'), $params, [
            'price'       => $price,
            'currency_id' => $currency,
            'action_url'  => $actionUrl,
            'order_detail_info'  => [
                [
                    'title'     => $title,
                    'link'      => $item?->toLink(),
                    'quantity'  => 1,
                    'price'     => $price,
                    'currency'  => $currency,
                    'sub_total' => 0,
                    'entity_type' => $item?->entityType(),
                    'entity_id'   => $item?->entityId(),
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
