<?php

namespace MetaFox\Localize\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;
use MetaFox\Form\Confirm;
use MetaFox\Localize\Http\Requests\v1\Currency\Admin\IndexRequest;
use MetaFox\Localize\Http\Requests\v1\Currency\Admin\StoreRequest;
use MetaFox\Localize\Http\Requests\v1\Currency\Admin\UpdateRequest;
use MetaFox\Localize\Http\Resources\v1\Currency\Admin\CurrencyDetail as Detail;
use MetaFox\Localize\Http\Resources\v1\Currency\Admin\CurrencyItemCollection as ItemCollection;
use MetaFox\Localize\Http\Resources\v1\Currency\Admin\StoreCurrencyForm;
use MetaFox\Localize\Http\Resources\v1\Currency\Admin\UpdateCurrencyForm;
use MetaFox\Localize\Repositories\CurrencyRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;
use MetaFox\Platform\Http\Requests\v1\OrderingRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class CurrencyAdminController.
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @ignore
 * @codeCoverageIgnore
 * @group admin/currency
 * @authenticated
 */
class CurrencyAdminController extends ApiController
{
    /**
     * @var CurrencyRepositoryInterface
     */
    public CurrencyRepositoryInterface $repository;

    /**
     * CurrencyAdminController constructor.
     *
     * @param CurrencyRepositoryInterface $repository
     *
     * @ignore
     */
    public function __construct(CurrencyRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse currencies.
     *
     * @param IndexRequest $request
     *
     * @return JsonResource
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @group admin/currency
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();
        $data   = $this->repository->viewCurrencies(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Create currency.
     *
     * @param StoreRequest $request
     *
     * @return Detail
     * @throws ValidatorException
     * @throws AuthorizationException|AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $this->repository->createCurrency(user(), $params);

        Artisan::call('cache:reset');

        return $this->success([], [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url'     => '/localize/currency/browse',
                    'replace' => true,
                ],
            ],
        ], __p('localize::admin.currency_successfully_created'));
    }

    /**
     * View currency.
     *
     * @param int $id
     *
     * @return Detail
     * @throws AuthorizationException|AuthenticationException
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->viewCurrency(user(), $id);

        return new Detail($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return Detail
     * @throws AuthorizationException|AuthenticationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $data = $this->repository->updateCurrency(user(), $id, $params);

        Artisan::call('cache:reset');

        return $this->success(new Detail($data), [], __p('localize::admin.currency_successfully_updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     * @group  admin/currency
     * @ignore
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteCurrency(user(), $id);

        Artisan::call('cache:reset');

        return $this->success([
            'id' => $id,
        ], [], __p('localize::admin.currency_deleted_successfully'));
    }

    /**
     * Update active status.
     *
     * @param ActiveRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws ValidationException|AuthenticationException
     * @group admin/currency
     * @ignore
     */
    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $context = user();

        $data = $request->validated();

        $isActive = (bool) Arr::get($data, 'active', true);

        if (!$this->repository->updateActive($context, $id, $isActive)) {
            return $this->error(__p('localize::admin.can_not_action_on_default_currency'));
        }

        $message = match ($isActive) {
            true  => __p('localize::admin.currency_successfully_activated'),
            false => __p('localize::admin.currency_successfully_deactived')
        };

        Artisan::call('cache:reset');

        return $this->success([
            'id'        => $id,
            'is_active' => $isActive,
        ], [], $message);
    }

    /**
     * Update default currency.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidationException
     * @ignore
     * @group admin/currency
     */
    public function toggleDefault(int $id): JsonResponse
    {
        $context = user();

        $this->repository->markAsDefault($context, $id);

        return $this->success([], [], __p('localize::admin.currency_successfully_set_as_default'));
    }

    /**
     * Update ordering.
     *
     * @param OrderingRequest $request
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     * @group  admin/currency
     * @ignore
     */
    public function order(OrderingRequest $request): JsonResponse
    {
        $params = $request->validated();
        $this->repository->orderCurrencies(user(), $params['orders']);

        return $this->success();
    }

    public function create(): JsonResponse
    {
        return $this->success(new StoreCurrencyForm());
    }

    /**
     * View form configuration.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @group  admin/currency
     * @ignore
     */
    public function edit(int $id): JsonResponse
    {
        $item = $this->repository->find($id);

        return $this->success(new UpdateCurrencyForm($item));
    }

    public function delete(): JsonResponse
    {
        return $this->success(new Confirm());
    }
}
