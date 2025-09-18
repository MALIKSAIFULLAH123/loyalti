<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Requests\v1\SubscriptionInvoice;

use MetaFox\Subscription\Http\Requests\v1\SubscriptionInvoice\CancelRequest;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionInvoiceController::cancel()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class CancelRequestTest.
 */
class CancelRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return CancelRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('reason_id'),
            $this->failIf('reason_id', 0, 'string', null),
        );
    }
}
