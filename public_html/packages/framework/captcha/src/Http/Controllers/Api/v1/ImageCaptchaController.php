<?php

namespace MetaFox\Captcha\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Captcha\Http\Controllers\Api\ImageCaptchaController::$controllers;
 */

/**
 * Class ImageCaptchaController.
 * @codeCoverageIgnore
 * @ignore
 */
class ImageCaptchaController extends ApiController
{
    /**
     * ImageCaptchaController Constructor.
     */
    public function __construct()
    {
    }

    public function refresh(Request $request): JsonResponse
    {
        $action = $request->get('action_name');

        $data = Captcha::refresh($action);

        return $this->success($data);
    }
}
