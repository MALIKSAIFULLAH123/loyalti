<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Requests\v1\SubscriptionCancelReason\Admin;

use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionCancelReasonAdminController::active()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class ActiveRequestTest.
 */
class ActiveRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return \MetaFox\Subscription\Http\Requests\v1\SubscriptionCancelReason\Admin\ActiveRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('active'),
            $this->passIf('active', 0, 1, true, false),
        );
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->asAdminUser();
    }
}
