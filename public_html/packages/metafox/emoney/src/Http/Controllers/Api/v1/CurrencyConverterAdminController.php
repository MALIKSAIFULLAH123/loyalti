<?php

namespace MetaFox\EMoney\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\EMoney\Jobs\GetExchangeRateJob;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\EMoney\Http\Resources\v1\CurrencyConverter\Admin\CurrencyConverterItemCollection as ItemCollection;
use MetaFox\EMoney\Http\Resources\v1\CurrencyConverter\Admin\CurrencyConverterDetail as Detail;
use MetaFox\EMoney\Repositories\CurrencyConverterRepositoryInterface;
use MetaFox\EMoney\Http\Requests\v1\CurrencyConverter\Admin\IndexRequest;
use MetaFox\EMoney\Http\Requests\v1\CurrencyConverter\Admin\UpdateRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\EMoney\Http\Controllers\Api\CurrencyConverterAdminController::$controllers;
 */

/**
 * Class CurrencyConverterAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class CurrencyConverterAdminController extends ApiController
{
    /**
     * @var CurrencyConverterRepositoryInterface
     */
    private CurrencyConverterRepositoryInterface $repository;

    /**
     * CurrencyConverterAdminController Constructor.
     *
     * @param CurrencyConverterRepositoryInterface $repository
     */
    public function __construct(CurrencyConverterRepositoryInterface $repository)
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
        $converters = $this->repository->viewConverters();

        return new ItemCollection($converters);
    }

    public function edit(Request $request, string $service): JsonResponse
    {
        $form = resolve(DriverRepositoryInterface::class)->getDriver('form-ewallet-converter', sprintf('ewallet.edit.%s', $service), 'admin');

        if (!class_exists($form)) {
            throw new AuthorizationException();
        }

        $instance = new $form();

        if (method_exists($instance, 'boot')) {
            app()->call([$instance, 'boot'], $request->all());
        }

        return $this->success($instance);
    }

    /**
     * Update item.
     *
     * @param  UpdateRequest      $request
     * @param  int                $id
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, string $service): JsonResponse
    {
        $converter = $this->repository->getConverter($service);

        $form = resolve(DriverRepositoryInterface::class)->getDriver('form-ewallet-converter', sprintf('ewallet.edit.%s', $service), 'admin');

        if (!class_exists($form)) {
            throw new AuthorizationException();
        }

        $data = $request->validated();

        $instance = new $form();

        if (method_exists($instance, 'validated')) {
            $formData = app()->call([$instance, 'validated']);

            if (is_array($formData)) {
                $data = array_merge($data, $formData);
            }
        }

        $converter->fill($data);

        $converter->save();

        if ($converter->is_default) {
            GetExchangeRateJob::dispatchSync();
        }

        $converter->refresh();

        return $this->success(new Detail($converter), [], __p('ewallet::admin.provider_was_updated_successfully'));
    }
}
