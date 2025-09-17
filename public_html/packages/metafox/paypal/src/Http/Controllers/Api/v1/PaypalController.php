<?php

namespace MetaFox\Paypal\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Paypal\Support\Paypal;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Paypal\Http\Controllers\Api\PaypalController::$controllers;
 */

/**
 * Class PaypalController.
 * @codeCoverageIgnore
 * @ignore
 */
class PaypalController extends ApiController
{
    /**
     * notify.
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function notify(Request $request): JsonResponse
    {
        $serviceName = Paypal::getGatewayServiceName();

        /** @var Paypal $service */
        $service = Payment::getManager()->getGatewayServiceByName($serviceName);
        $status  = $service->handleWebhook([
            'request' => $request
        ]);

        if ($status) {
            return $this->success();
        }

        return $this->error();
    }
}
