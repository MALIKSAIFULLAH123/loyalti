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
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionCancelReasonAdminController::update()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class UpdateRequestTest.
 */
class UpdateRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return \MetaFox\Subscription\Http\Requests\v1\SubscriptionCancelReason\Admin\UpdateRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('title'),
            $this->passIf('title', fake()->words(3, true)),
            $this->failIf('title', '', null, 0, str_pad('A', 500, 'A')),
        );
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->asAdminUser();
    }
}
