<?php

namespace MetaFox\Sms\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * Class ServiceController.
 * @codeCoverageIgnore
 * @ignore
 */
class ServiceController extends ApiController
{
    /**
     * notify.
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function notify(Request $request): JsonResponse
    {
        // TODO: implement logic to handle webhook

        return $this->success();
    }
}
