<?php

namespace MetaFox\EMoney\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\EMoney\Http\Requests\v1\ConversionRate\Admin\IndexRequest;
use MetaFox\EMoney\Http\Requests\v1\ConversionRate\Admin\UpdateRequest;
use MetaFox\EMoney\Http\Resources\v1\ConversionRate\Admin\ConversionRateDetail as Detail;
use MetaFox\EMoney\Http\Resources\v1\ConversionRate\Admin\ConversionRateItemCollection as ItemCollection;
use MetaFox\EMoney\Http\Resources\v1\ConversionRate\Admin\EditConversionRateForm;
use MetaFox\EMoney\Models\ConversionRate;
use MetaFox\EMoney\Services\Contracts\ConversionRateServiceInterface;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\EMoney\Http\Controllers\Api\ConversionRateAdminController::$controllers;
 */

/**
 * Class ConversionRateAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class ConversionRateAdminController extends ApiController
{
    public function __construct(private ConversionRateServiceInterface $service)
    {
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $target = Arr::get($params, 'target', Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE);
        $data   = $this->service->viewConversionRates($target);

        return new ItemCollection($data);
    }

    /**
     * Update item.
     *
     * @param UpdateRequest $request
     * @param int           $id
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $data = $this->service->updateSetting($id, $params);

        return $this->success(new Detail($data), [], __p('ewallet::admin.exchange_rate_was_updated_successfully'));
    }

    public function edit(int $id): JsonResponse
    {
        $rate = ConversionRate::query()->findOrFail($id);

        $form = new EditConversionRateForm($rate);

        return $this->success($form);
    }
}
