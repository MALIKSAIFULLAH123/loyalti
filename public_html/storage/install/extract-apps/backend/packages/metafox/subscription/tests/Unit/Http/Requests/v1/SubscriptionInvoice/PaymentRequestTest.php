<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Requests\v1\SubscriptionInvoice;

use MetaFox\Subscription\Http\Requests\v1\SubscriptionInvoice\PaymentRequest;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionInvoiceController::payment()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class PaymentRequestTest.
 */
class PaymentRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return PaymentRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('action_type'),
            $this->failIf('action_type', 0, 'string', [], ['string']),
            $this->passIf('action_type', 'upgrade', 'pay_now')
        );
    }
}
