<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Requests\v1\SubscriptionInvoice;

use MetaFox\Subscription\Http\Requests\v1\SubscriptionInvoice\RenewMethodRequest;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionInvoiceController::renewMethod()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class RenewMethodRequestTest.
 */
class RenewMethodRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return RenewMethodRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('payment_gateway', 'action_type', 'id'),
            $this->failIf('payment_gateway', 0, 'string'),
            $this->failIf('action_type', 0, 'string'),
            $this->passIf('action_type', 'upgrade', 'pay_now')
        );
    }
}
