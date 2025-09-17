<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Requests\v1\SubscriptionInvoice;

use MetaFox\Subscription\Http\Requests\v1\SubscriptionInvoice\RenewRequest;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionInvoiceController::renew()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class RenewRequestTest.
 */
class RenewRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return RenewRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('payment_gateway'),
            $this->failIf('payment_gateway', 'string', 0, null),
        );
    }
}
