<?php

namespace MetaFox\InAppPurchase\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Payment\Http\Resources\v1\Gateway\Admin\GatewayForm;
use MetaFox\Payment\Http\Resources\v1\Gateway\GatewayDetail as Detail;
use MetaFox\Payment\Repositories\GatewayRepositoryInterface;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Platform\Contracts\SiteSettingRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\InAppPurchase\Repositories\GoogleServiceAccountRepositoryInterface;
use MetaFox\InAppPurchase\Http\Requests\v1\GoogleServiceAccount\Admin\StoreRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\InAppPurchase\Http\Controllers\Api\GoogleServiceAccountAdminController::$controllers;
 */

/**
 * Class GoogleServiceAccountAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class GoogleServiceAccountAdminController extends ApiController
{
    /**
     * @var GoogleServiceAccountRepositoryInterface
     */
    private GoogleServiceAccountRepositoryInterface $repository;

    /**
     * GoogleServiceAccountAdminController Constructor.
     *
     * @param GoogleServiceAccountRepositoryInterface $repository
     */
    public function __construct(GoogleServiceAccountRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $this->repository->create($params);

        $nextAction = ['type' => 'navigate', 'payload' => ['url' => '/in-app-purchase/setting/google-service-account']];

        return $this->success([], ['nextAction' => $nextAction], __p('in-app-purchase::phrase.google_service_account_updated_successfully'));
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->success([
            'id' => $id,
        ]);
    }

    public function updateGateway(Request $request, int $id): JsonResponse
    {
        $form = Payment::getGatewayAdminFormById($id);

        if (!$form instanceof GatewayForm) {
            return $this->error();
        }

        $params           = $form->validated($request);
        $params['config'] = Arr::except($params, [
            'title',
            'description',
            'is_test',
            'is_active',
        ]);

        $data = resolve(GatewayRepositoryInterface::class)->updateGateway(user(), $id, $params);

        $package = 'in-app-purchase';

        $privateSettings = [];

        $settings = $request->all();

        resolve(SiteSettingRepositoryInterface::class)->saveAndCollectPrivateSettings($settings, $privateSettings);

        Artisan::call('cache:reset');

        app('events')->dispatch('site_settings.updated', $package);

        return $this->success(new Detail($data), [], __p('core::phrase.updated_successfully'));
    }
}
