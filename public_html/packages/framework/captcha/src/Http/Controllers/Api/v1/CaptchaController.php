<?php

namespace MetaFox\Captcha\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Captcha\Http\Requests\v1\Captcha\VerifyRequest;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Captcha\Http\Controllers\Api\CaptchaController::$controllers;
 */

/**
 * Class CaptchaController.
 * @codeCoverageIgnore
 * @ignore
 */
class CaptchaController extends ApiController
{
    /**
     * CaptchaController Constructor.
     */
    public function __construct()
    {
    }

    public function verify(VerifyRequest $request): JsonResponse
    {
        $request->validated();

        return $this->success([
            'authorized' => true,
        ]);
    }
}
