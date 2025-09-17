<?php

namespace MetaFox\Marketplace\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Constants;
use MetaFox\Marketplace\Http\Requests\v1\Invoice\CancelRequest;
use MetaFox\Marketplace\Http\Requests\v1\Invoice\ChangeRequest;
use MetaFox\Marketplace\Http\Requests\v1\Invoice\IndexRequest;
use MetaFox\Marketplace\Http\Requests\v1\Invoice\PaymentRequest;
use MetaFox\Marketplace\Http\Requests\v1\Invoice\StoreRequest;
use MetaFox\Marketplace\Http\Requests\v1\Invoice\UpdateRequest;
use MetaFox\Marketplace\Http\Resources\v1\Invoice\InvoiceDetail;
use MetaFox\Marketplace\Http\Resources\v1\Invoice\InvoiceDetail as Detail;
use MetaFox\Marketplace\Http\Resources\v1\Invoice\InvoiceItemCollection as ItemCollection;
use MetaFox\Marketplace\Policies\InvoicePolicy;
use MetaFox\Marketplace\Repositories\InvoiceRepositoryInterface;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Marketplace\Support\Facade\Listing as ListingFacade;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Payment\Traits\Controller\HandleExtraPaymentParamsTrait;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\SEO\ActionMeta;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Marketplace\Http\Controllers\Api\InvoiceController::$controllers;.
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
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->viewInvoices($context, $params);

        return new ItemCollection($data);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $listingId = Arr::get($params, 'id', 0);

        $gatewayId = Arr::get($params, 'payment_gateway', 0);

        $nextPaymentForm = $this->getNextPaymentForm($context, $params);

        if ($nextPaymentForm instanceof AbstractForm) {
            return $this->success($nextPaymentForm, $nextPaymentForm->getMultiStepFormMeta());
        }

        $data = $this->repository->createInvoice($context, $listingId, $gatewayId, $this->getExtraParamsPayment($params));

        $status = Arr::get($data, 'status', false);

        if (false === $status) {
            return $this->error(__p('marketplace::phrase.can_not_create_order_for_listing_purchasement'));
        }

        return $this->success([
            'url'   => Arr::get($data, 'gateway_redirect_url'),
            'token' => Arr::get($data, 'gateway_token'),
        ], $this->getMetaData());
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

    public function change(ChangeRequest $request): JsonResponse
    {
        $data = $request->validated();

        $context = user();

        $id = Arr::get($data, 'id');

        $invoice = $this->repository->changeInvoice($context, $id);

        if (null === $invoice) {
            return $this->error(__p('marketplace::phrase.can_not_change_the_invoice'));
        }

        $model = $this->repository->find($id);
        return $this->success(new InvoiceDetail($model));
    }

    /**
     * @param CancelRequest $request
     * @param int           $id
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function cancel(CancelRequest $request, int $id): JsonResponse
    {
        $data      = $request->validated();
        $listingId = Arr::get($data, 'listing_id');
        $invoice   = $this->repository->find($id);
        $context   = user();

        policy_authorize(InvoicePolicy::class, 'cancel', $context, $invoice);

        if (null === $invoice) {
            return $this->error(__p('marketplace::phrase.can_not_change_the_invoice'));
        }

        if ($listingId) {
            $this->repository->getModel()->newQuery()
                ->where('status', ListingFacade::getInitPaymentStatus())
                ->where('listing_id', $listingId)
                ->update(['status' => ListingFacade::getCanceledPaymentStatus()]);

            return $this->success(new InvoiceDetail($invoice->refresh()), [], __p('marketplace::phrase.invoice_s_was_deleted_successfully'));
        }

        $invoice->update(['status' => ListingFacade::getCanceledPaymentStatus()]);

        return $this->success(new InvoiceDetail($invoice), [], __p('marketplace::phrase.invoice_s_was_deleted_successfully'));
    }

    public function repayment(PaymentRequest $request, int $id): JsonResponse
    {
        $context = user();

        $params = $request->validated();

        $gatewayId = Arr::get($params, 'payment_gateway');

        $invoice = $this->repository->find($id);

        Arr::set($params, 'id', $invoice->listing_id);

        $nextPaymentForm = $this->getNextPaymentForm($context, $params, sprintf('marketplace-invoice/repayment/%s', $id), Constants::METHOD_PUT);

        if ($nextPaymentForm instanceof AbstractForm) {
            return $this->success($nextPaymentForm, $nextPaymentForm->getMultiStepFormMeta());
        }

        $data = $this->repository->repaymentInvoice($context, $id, $gatewayId, $this->getExtraParamsPayment($params));

        $status = Arr::get($data, 'status', false);

        if (false === $status) {
            return $this->error(__p('marketplace::phrase.can_not_create_order_for_listing_purchasement'));
        }

        return $this->success([
            'url'   => Arr::get($data, 'gateway_redirect_url'),
            'token' => Arr::get($data, 'gateway_token'),
        ], $this->getMetaData());
    }

    protected function getExtraParamsPayment(array $params): array
    {
        $paymentCurrency = Arr::get($params, 'payment_gateway_balance_currency');
        $extra           = [];

        if ($paymentCurrency) {
            Arr::set($extra, 'currency_payment', $paymentCurrency);
        }

        return $extra;
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

    protected function getNextPaymentForm($context, array $params, string $action = 'marketplace-invoice', string $requestMethod = Constants::METHOD_POST): ?AbstractForm
    {
        $listing = resolve(ListingRepositoryInterface::class)->find(Arr::get($params, 'id'));

        $values = ListingFacade::getFormValues($context, $listing);

        return Payment::getNextPaymentForm($context, Arr::get($params, 'payment_gateway'), $params, [
            'price'             => Arr::get($values, 'price'),
            'currency_id'       => Arr::get($values, 'currency_id'),
            'method'            => $requestMethod,
            'action_url'        => $action,
            'order_detail_info' => [
                [
                    'title'       => __p('marketplace::phrase.sponsor_title', [
                        'title' => $listing->toTitle(),
                    ]),
                    'link'        => $listing->toLink(),
                    'quantity'    => 1,
                    'price'       => Arr::get($values, 'price'),
                    'currency'    => Arr::get($values, 'currency_id'),
                    'sub_total'   => 0,
                    'entity_type' => $listing->entityType(),
                    'entity_id'   => $listing->entityId(),
                ],
            ],
        ]);
    }
}
