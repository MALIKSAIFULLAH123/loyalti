<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Requests\v1\SubscriptionInvoice;

use MetaFox\Subscription\Http\Requests\v1\SubscriptionInvoice\UpgradeRequest;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionInvoiceController::upgrade()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class UpgradeRequestTest.
 */
class UpgradeRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return UpgradeRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('payment_gateway', 'action_type'),
            $this->failIf('payment_gateway', 'string', 0),
            $this->failIf('action_type', ''),
            $this->failIf('renew_type', 'string', 0),
            $this->passIf('action_type', 'upgrade', 'pay_now'),
            $this->passIf('renew_type', 'manual', 'auto')
        );
    }
}
