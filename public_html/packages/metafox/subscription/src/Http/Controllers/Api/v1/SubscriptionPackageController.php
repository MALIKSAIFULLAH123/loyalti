<?php

namespace MetaFox\Subscription\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionPackage\IndexRequest;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionPackage\RenewRequest;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\FirstFreeRecurringPackageForm;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\PaymentSubscriptionPackageForm;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\RenewSubscriptionPackageForm;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\SubscriptionPackageDetail as Detail;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\SubscriptionPackageItemCollection as ItemCollection;
use MetaFox\Subscription\Models\SubscriptionPackage;
use MetaFox\Subscription\Policies\SubscriptionInvoicePolicy;
use MetaFox\Subscription\Policies\SubscriptionPackagePolicy;
use MetaFox\Subscription\Repositories\SubscriptionInvoiceRepositoryInterface;
use MetaFox\Subscription\Repositories\SubscriptionPackageRepositoryInterface;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage as Facade;
use MetaFox\Subscription\Support\Helper;
use MetaFox\User\Support\Facades\User;
use MetaFox\Platform\Contracts\User as UserContract;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Subscription\Http\Controllers\Api\SubscriptionPackageController::$controllers;
 */

/**
 * Class SubscriptionPackageController.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class SubscriptionPackageController extends ApiController
{
    /**
     * @var SubscriptionPackageRepositoryInterface
     */
    private SubscriptionPackageRepositoryInterface $repository;

    /**
     * SubscriptionPackageController Constructor.
     *
     * @param SubscriptionPackageRepositoryInterface $repository
     */
    public function __construct(SubscriptionPackageRepositoryInterface $repository)
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
    public function index(IndexRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = User::getGuestUser();

        if (Auth::id() != MetaFoxConstant::GUEST_USER_ID) {
            $context = user();
        }

        $page = Arr::get($params, 'page');

        /*
         * Handle load more issue for old mobile version <= 5.1.14
         */
        if (is_numeric($page) && $page > 1) {
            return $this->success();
        }

        $data = $this->repository->viewPackages($context, $params);

        return $this->success(new ItemCollection($data));
    }

    /**
     * View item.
     *
     * @param int $id
     *
     * @return Detail
     */
    public function show($id): Detail
    {
        $context = user();

        $data = $this->repository->viewPackage($context, $id, [
            'view' => Helper::VIEW_FILTER,
        ]);

        return new Detail($data);
    }

    protected function responseFirstFreeRecurring(UserContract $context, SubscriptionPackage $package, array $meta = []): JsonResponse
    {
        $form = new FirstFreeRecurringPackageForm();

        $result = resolve(SubscriptionInvoiceRepositoryInterface::class)->createInvoice($context, [
            'id' => $package->entityId(),
            'renew_type' => Helper::RENEW_TYPE_MANUAL,
        ]);

        if (!Arr::get($result, 'is_first_free')) {
            return $this->error();
        }

        return $this->success($form, $meta);
    }

    protected function handleFirstFreeRecurring(UserContract $context, SubscriptionPackage $package, array $params): ?JsonResponse
    {
        $renewType = Arr::get($params, 'renew_type');

        if (!is_string($renewType) && Facade::hasOnlySpecificMethod($package, Helper::RENEW_TYPE_MANUAL)) {
            return $this->responseFirstFreeRecurring($context, $package);
        }

        if ($renewType === Helper::RENEW_TYPE_MANUAL) {
            return $this->responseFirstFreeRecurring($context, $package, [
                'continueAction' => [
                    'type'    => 'multiStepForm/next',
                    'payload' => [
                        'formName'               => 'subscription_payment_form',
                        'processChildId'         => 'subscription_get_gateway_form',
                        'previousProcessChildId' => 'subscription_get_renew_form',
                    ],
                ],
            ]);
        }

        return null;
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    public function getPaymentPackageForm(RenewRequest $request, int $id): JsonResponse
    {
        $package   = $this->repository->find($id);
        $params    = $request->validated();
        $renewType = Arr::get($params, 'renew_type');
        $context   = user();

        policy_authorize(SubscriptionPackagePolicy::class, 'purchase', $context, $package);

        $response = null;

        if (Facade::isFirstFreeAndRecurringForUser($context, $package)) {
            $response = $this->handleFirstFreeRecurring($context, $package, $params);
        }

        if ($response instanceof JsonResponse) {
            return $response;
        }

        $isFree = false;

        if (Facade::isFreePackageForUser($context, $package)) {
            $result = resolve(SubscriptionInvoiceRepositoryInterface::class)->createInvoice($context, [
                'id' => $id,
            ]);

            if (!Arr::get($result, 'is_free')) {
                return $this->error();
            }

            $isFree = true;
        }

        $form = new PaymentSubscriptionPackageForm($package);

        $meta = [];

        if (Helper::isUsingMultiStepFormForEwallet()) {
            $meta = [
                'continueAction' => [
                    'type'    => 'multiStepForm/next',
                    'payload' => [
                        'formName'               => 'subscription_payment_form',
                        'processChildId'         => 'subscription_invoice_get_gateway_form',
                        'previousProcessChildId' => 'subscription_invoice_get_renew_form',
                    ],
                ],
            ];
            $form->setPreviousProcessChildId('subscription_invoice_get_gateway_form');
            $form->setPreviousCustomAction('subscription_invoice_get_renew_form');
        }

        if (!$isFree && $package->is_recurring) {
            if (empty($renewType)) {
                return $this->getRenewForm($id);
            } else {
                $form->setIsRecurring($renewType == Helper::RENEW_TYPE_AUTO)
                    ->setRenewType($renewType);

                $meta = [
                    'continueAction' => [
                        'type'    => 'multiStepForm/next',
                        'payload' => [
                            'formName'               => 'subscription_payment_form',
                            'processChildId'         => 'subscription_get_gateway_form',
                            'previousProcessChildId' => 'subscription_get_renew_form',
                        ],
                    ],
                ];

                $form->setPreviousProcessChildId('subscription_get_gateway_form');
                $form->setPreviousCustomAction('subscription_get_renew_form');

                $form->setSteps([
                    'total_steps'  => 2,
                    'current_step' => 2,
                ]);
            }
        }

        return $this->success($form, $meta);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function getRenewForm(int $id): JsonResponse
    {
        $package = $this->repository->find($id);

        $context = user();

        policy_authorize(SubscriptionInvoicePolicy::class, 'chooseRenewType', $context, $package);

        $meta = [
            'continueAction' => [
                'type'    => 'multiStepForm/next',
                'payload' => [
                    'formName'               => 'subscription_payment_form',
                    'processChildId'         => 'subscription_get_renew_form',
                    'previousProcessChildId' => null,
                ],
            ],
        ];

        $form = new RenewSubscriptionPackageForm($package);

        $form->setSteps([
            'total_steps'  => 2,
            'current_step' => 1,
        ]);

        return $this->success($form, $meta);
    }
}
