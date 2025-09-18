<?php

namespace MetaFox\Subscription\Http\Controllers\Api\v1;

use Facebook\Exception\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Constants;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Payment\Traits\Controller\HandleExtraPaymentParamsTrait;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionInvoice\CancelRequest;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionInvoice\IndexRequest;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionInvoice\RenewMethodRequest;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionInvoice\RenewRequest;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionInvoice\StoreRequest;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionInvoice\UpgradeRequest;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\CancelSubscriptionInvoiceForm;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\PaymentSubscriptionInvoiceForm;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\RenewMethodForm;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\RenewSubscriptionInvoiceForm;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\SubscriptionInvoiceCancelDetail;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\SubscriptionInvoiceDetail as Detail;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\SubscriptionInvoiceItemCollection as ItemCollection;
use MetaFox\Subscription\Models\SubscriptionInvoice;
use MetaFox\Subscription\Models\SubscriptionPackage;
use MetaFox\Subscription\Policies\SubscriptionInvoicePolicy;
use MetaFox\Subscription\Repositories\SubscriptionInvoiceRepositoryInterface;
use MetaFox\Subscription\Repositories\SubscriptionPackageRepositoryInterface;
use MetaFox\Subscription\Support\Helper;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Subscription\Http\Controllers\Api\SubscriptionInvoiceController::$controllers;
 */

/**
 * Class SubscriptionInvoiceController.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class SubscriptionInvoiceController extends ApiController
{
    use HandleExtraPaymentParamsTrait;

    /**
     * @var SubscriptionInvoiceRepositoryInterface
     */
    private SubscriptionInvoiceRepositoryInterface $repository;

    /**
     * SubscriptionInvoiceController Constructor.
     *
     * @param SubscriptionInvoiceRepositoryInterface $repository
     */
    public function __construct(SubscriptionInvoiceRepositoryInterface $repository)
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
     * @return Detail
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $paymentParams = $request->validated();

        $context = user();

        Arr::forget($params, ['previous_process_child_id', 'form_name']);

        $renewType = Arr::get($params, 'renew_type');

        $gatewayId = Arr::get($params, 'payment_gateway');

        $isRecurring = $renewType == Helper::RENEW_TYPE_AUTO;

        /**
         * TODO: Remove after supporting recurring in E-Wallet
         */
        if ($isRecurring) {
            $gateway = Gateway::query()
                ->where([
                    'id' => $gatewayId,
                ])
                ->first();

            if (!$gateway instanceof Gateway || $gateway->service == 'ewallet') {
                throw new \Illuminate\Auth\Access\AuthorizationException();
            }
        }

        $package = resolve(SubscriptionPackageRepositoryInterface::class)->find(Arr::get($params, 'id'));

        $userCurrency = app('currency')->getUserCurrencyId($context);

        $price = json_decode($package->price, true);

        $nextPaymentForm = $this->getNextPaymentForm($context, 'subscription-invoice', $params, array_merge($paymentParams, [
            'price'        => Arr::get($price, $userCurrency),
            'currency_id'  => $userCurrency,
            'is_recurring' => $isRecurring,
            'package'      => $package,
        ]));

        if ($nextPaymentForm instanceof AbstractForm) {
            return $this->success($nextPaymentForm, $nextPaymentForm->getMultiStepFormMeta());
        }

        $result = $this->repository->createInvoice($context, $params, $this->getExtraPaymentParams($context, $params));

        if (!count($result)) {
            return $this->error(__p('subscription::validation.can_not_create_order_for_package_purchasement'));
        }

        return $this->success([
            'url'   => Arr::get($result, 'gateway_redirect_url'),
            'token' => Arr::get($result, 'gateway_token'),
        ], ['continueAction' => ['type' => 'multiStepForm/subscription/done']], Arr::get($result, 'message'));
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
     * @param int $id
     *
     * @return CancelSubscriptionInvoiceForm
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws AuthenticationException
     */
    public function getCancelSubscriptionForm(int $id): CancelSubscriptionInvoiceForm
    {
        $invoice = $this->repository->find($id);

        $context = user();

        policy_authorize(SubscriptionInvoicePolicy::class, 'cancel', $context, $invoice);

        return new CancelSubscriptionInvoiceForm($invoice);
    }

    /**
     * @param CancelRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function cancel(CancelRequest $request, int $id): JsonResponse
    {
        $context = user();

        $params = $request->validated();

        $this->repository->cancelSubscriptionByUser($context, $id, $params);

        $invoice = $this->repository->find($id);

        return $this->success(new SubscriptionInvoiceCancelDetail($invoice), [], __p('subscription::phrase.subscription_successfully_cancelled'));
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws AuthenticationException
     */
    public function getRenewSubscriptionForm(int $id): JsonResponse
    {
        $invoice = $this->repository
            ->with(['package'])
            ->find($id);

        $context = user();

        policy_authorize(SubscriptionInvoicePolicy::class, 'renew', $context, $invoice);

        $form = new RenewSubscriptionInvoiceForm($invoice);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot']);
        }

        return $this->success($form, $form->getFormMeta());
    }

    /**
     * @param RenewRequest $request
     * @param int          $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function renew(RenewRequest $request, int $id): JsonResponse
    {
        $params = $data = $request->validated();

        Arr::forget($data, ['previous_process_child_id', 'form_name']);

        $context = user();

        $invoice = $this->repository->find($id);

        if (!$invoice instanceof SubscriptionInvoice) {
            throw new AuthorizationException();
        }

        $paymentParams = $this->handleParamsBeforePayment($id, $params);

        $url = url_utility()->makeApiUrl('subscription-invoice/renew/' . $id);

        $nextPaymentForm = $this->getNextPaymentForm($context, $url, $data, array_merge($paymentParams, [
            'package' => $invoice->package,
        ]));

        if ($nextPaymentForm instanceof AbstractForm) {
            return $this->success($nextPaymentForm, $nextPaymentForm->getMultiStepFormMeta());
        }

        $result = $this->repository->renewInvoice($context, $id, $data, $this->getExtraPaymentParams($context, $data));

        if (!count($result)) {
            return $this->error(__p('subscription::phrase.renew_progress_has_been_failed'));
        }

        return $this->success([
            'url'   => Arr::get($result, 'gateway_redirect_url'),
            'token' => Arr::get($result, 'gateway_token'),
        ]);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function change(int $id): JsonResponse
    {
        $context = user();

        $invoice = $this->repository->changeInvoice($context, $id);

        if (null === $invoice) {
            return $this->error(__p('subscription::phrase.can_not_change_invoice'));
        }

        return $this->success(ResourceGate::asResource($invoice, 'item'));
    }

    /**
     * @param RenewMethodRequest $request
     * @param int                $id
     *
     * @return JsonResponse
     */
    public function getPaymentForm(RenewMethodRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        $renewType = Arr::get($data, 'renew_type');

        $actionType = Arr::get($data, 'action_type');

        $invoice = $this->repository->find($id);

        $context = user();

        switch ($actionType) {
            case Helper::UPGRADE_FORM_ACTION:
                policy_authorize(SubscriptionInvoicePolicy::class, 'upgrade', $context, $invoice);
                break;
            case Helper::PAY_NOW_FORM_ACTION:
                policy_authorize(SubscriptionInvoicePolicy::class, 'payNow', $context, $invoice);
                break;
            default:
                throw new AuthorizationException();
        }

        $form = new PaymentSubscriptionInvoiceForm($invoice, $actionType);

        $meta = [];

        if (Helper::isUsingMultiStepFormForEwallet()) {
            $meta = [
                'continueAction' => [
                    'type'    => 'multiStepForm/next',
                    'payload' => [
                        'formName'               => 'subscription_invoice_payment_form',
                        'processChildId'         => 'subscription_invoice_get_gateway_form',
                        'previousProcessChildId' => 'subscription_invoice_get_renew_form',
                    ],
                ],
            ];

            $form->setPreviousProcessChildId('subscription_invoice_get_gateway_form');
            $form->setPreviousCustomAction('subscription_invoice_get_renew_form');
        }

        if ($invoice->is_recurring && $actionType == Helper::UPGRADE_FORM_ACTION) {
            if (empty($renewType)) {
                return $this->getRenewMethodForm($request, $id);
            } else {
                $form->setIsRecurring($renewType == Helper::RENEW_TYPE_AUTO)
                    ->setRenewType($renewType);

                $meta = [
                    'continueAction' => [
                        'type'    => 'multiStepForm/next',
                        'payload' => [
                            'formName'               => 'subscription_invoice_payment_form',
                            'processChildId'         => 'subscription_invoice_get_gateway_form',
                            'previousProcessChildId' => 'subscription_invoice_get_renew_form',
                        ],
                    ],
                ];

                $form->setPreviousProcessChildId('subscription_invoice_get_gateway_form');
                $form->setPreviousCustomAction('subscription_invoice_get_renew_form');

                $form->setSteps([
                    'total_steps'  => 2,
                    'current_step' => 2,
                ]);
            }
        }

        return $this->success($form, $meta);
    }

    /**
     * @param RenewMethodRequest $request
     * @param int                $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws AuthenticationException
     */
    public function getRenewMethodForm(RenewMethodRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        $invoice = $this->repository->find($id);

        $actionType = Arr::get($data, 'action_type');

        $context = user();

        switch ($actionType) {
            case Helper::UPGRADE_FORM_ACTION:
                policy_authorize(SubscriptionInvoicePolicy::class, 'upgrade', $context, $invoice);
                break;
            case Helper::PAY_NOW_FORM_ACTION:
                policy_authorize(SubscriptionInvoicePolicy::class, 'payNow', $context, $invoice);
                break;
            default:
                throw new AuthorizationException();
        }

        $form = new RenewMethodForm($invoice, $actionType);

        $form->setSteps([
            'total_steps'  => 2,
            'current_step' => 1,
        ]);

        return $this->success($form, [
            'continueAction' => [
                'type'    => 'multiStepForm/next',
                'payload' => [
                    'formName'               => 'subscription_invoice_payment_form',
                    'processChildId'         => 'subscription_invoice_get_renew_form',
                    'previousProcessChildId' => null,
                ],
            ],
        ]);
    }

    /**
     * @param UpgradeRequest $request
     * @param int            $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function upgrade(UpgradeRequest $request, int $id): JsonResponse
    {
        $params = $data = $request->validated();

        /**
         * @var SubscriptionInvoice $invoice
         */
        $invoice = $this->repository->find($id);

        Arr::forget($data, ['previous_process_child_id', 'form_name']);

        $context = user();

        $renewType = Arr::get($data, 'renew_type');

        $gatewayId = Arr::get($data, 'payment_gateway');

        $isRecurring = $renewType == Helper::RENEW_TYPE_AUTO;

        /**
         * TODO: Remove after supporting recurring in E-Wallet
         */
        if ($isRecurring) {
            $gateway = Gateway::query()
                ->where([
                    'id' => $gatewayId,
                ])
                ->first();

            if (!$gateway instanceof Gateway || $gateway->service == 'ewallet') {
                throw new \Illuminate\Auth\Access\AuthorizationException();
            }
        }

        $url = url_utility()->makeApiUrl('subscription-invoice/upgrade/' . $id);

        $paymentParams = $this->handleParamsBeforePayment($id, $params);

        $nextPaymentForm = $this->getNextPaymentForm($context, $url, $data, array_merge($paymentParams, [
            'is_recurring' => $isRecurring,
            'package'      => $invoice->package,
        ]));

        if ($nextPaymentForm instanceof AbstractForm) {
            return $this->success($nextPaymentForm, $nextPaymentForm->getMultiStepFormMeta());
        }

        $result = $this->repository->upgrade($context, $id, $data, $this->getExtraPaymentParams($context, $data));

        if (null === $result) {
            return $this->error(__p('subscription::phrase.can_not_upgrade_this_invoice'));
        }

        $isFree = Arr::get($result, 'is_free');

        if ($isFree === true) {
            return $this->success([], [
                'continueAction' => ['type' => 'multiStepForm/subscription/forceReload'],
            ], __p('subscription::phrase.your_membership_has_successfully_been_upgraded'));
        }

        return $this->success([
            'url'   => Arr::get($result, 'gateway_redirect_url'),
            'token' => Arr::get($result, 'gateway_token'),
        ], ['continueAction' => ['type' => 'multiStepForm/subscription/done']]);
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

    protected function getNextPaymentForm(User $context, string $url, array $requestPayloads, array $paymentParams): ?AbstractForm
    {
        /**
         * @var SubscriptionPackage $package
         */
        $package = Arr::get($paymentParams, 'package');

        $title = null;

        if ($package instanceof SubscriptionPackage) {
            $title = __p('subscription::phrase.subscription_package_title', [
                'title' => $package->toTitle(),
            ]);
        }

        return Payment::getNextPaymentForm($context, Arr::get($requestPayloads, 'payment_gateway'), $requestPayloads, array_merge($paymentParams, [
            'action_url'        => $url,
            'order_detail_info' => [
                [
                    'title'       => $title,
                    'link'        => '/subscription/package',
                    'quantity'    => 1,
                    'price'       => Arr::get($paymentParams, 'price'),
                    'currency'    => Arr::get($paymentParams, 'currency_id'),
                    'sub_total'   => 0,
                    'entity_type' => $package?->entityType(),
                    'entity_id'   => $package?->entityId(),
                ],
            ],
        ]));
    }

    /**
     * @param int   $id
     * @param array $params
     *
     * @return array
     * @throws AuthorizationException
     */
    private function handleParamsBeforePayment(int $id, array $params): array
    {
        $invoice = $this->repository->find($id);

        if (!$invoice instanceof SubscriptionInvoice) {
            throw new AuthorizationException();
        }

        return array_merge($params, [
            'price'       => $invoice->total,
            'currency_id' => $invoice->currency,
            'method'      => Constants::METHOD_PATCH,
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function getMyActiveSubscription(): JsonResponse
    {
        $context = user();

        if ($context->isGuest()) {
            return $this->success();
        }

        $subscription = $this->repository->getUserActiveSubscription($context);

        if (!$subscription instanceof SubscriptionInvoice) {
            return $this->success();
        }

        if (!policy_check(SubscriptionInvoicePolicy::class, 'view', $context, $subscription)) {
            return $this->success();
        }

        return $this->success(ResourceGate::asItem($subscription, null));
    }
}
