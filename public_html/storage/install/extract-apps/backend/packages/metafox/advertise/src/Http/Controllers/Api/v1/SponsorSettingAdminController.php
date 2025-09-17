<?php

namespace MetaFox\Advertise\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Advertise\Services\Contracts\SponsorSettingServiceInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Advertise\Http\Controllers\Api\SponsorSettingAdminController::$controllers;
 */

/**
 * Class SponsorSettingAdminController.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class SponsorSettingAdminController extends ApiController
{
    /**
     * @var SponsorSettingServiceInterface
     */
    private SponsorSettingServiceInterface $service;

    /**
     * SponsorSettingAdminController Constructor.
     *
     * @param SponsorSettingServiceInterface $service
     */
    public function __construct(SponsorSettingServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Update item.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $params = $request->all();

        $context = user();

        $params = $this->transformPermissions($params);

        $params = $this->transformSettings($params);

        $this->service->updateSettings($context, $id, $params);

        Artisan::call('cache:reset');

        return $this->success([], [], __p('advertise::phrase.settings_successfully_updated'));
    }

    protected function transformPermissions(array $params): array
    {
        $permissions = Arr::get($params, 'permissions');

        if (!is_array($permissions)) {
            return $params;
        }

        $permissions = Arr::dot($permissions);

        return array_merge($params, [
            'permissions' => $permissions,
        ]);
    }

    protected function transformSettings(array $params): array
    {
        foreach ($params as $key => $param) {
            if (preg_match('/^settings_(.*)_(.*)$/', $key, $matches)) {
                $params['settings'][$matches[1]][$matches[2]] = $param;
                unset($params[$key]);
            }
        }

        return $params;
    }
}
