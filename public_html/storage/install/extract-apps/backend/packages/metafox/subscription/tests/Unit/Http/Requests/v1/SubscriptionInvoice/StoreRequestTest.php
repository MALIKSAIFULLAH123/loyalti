<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Requests\v1\SubscriptionInvoice;

use MetaFox\Subscription\Http\Requests\v1\SubscriptionInvoice\StoreRequest;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionInvoiceController::store()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class StoreRequestTest.
 */
class StoreRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return StoreRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('payment_gateway', 'id'),
            $this->failIf('id', 0, 'string'),
            $this->failIf('payment_gateway', 0, 'string'),
            $this->passIf('renew_type', 'manual', 'auto'),
        );
    }
}
