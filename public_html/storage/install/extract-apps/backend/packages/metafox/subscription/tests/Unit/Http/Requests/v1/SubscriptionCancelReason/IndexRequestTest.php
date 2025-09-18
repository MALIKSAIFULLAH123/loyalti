<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Requests\v1\SubscriptionCancelReason;

use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionCancelReasonController::index()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class IndexRequestTest.
 */
class IndexRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return \MetaFox\Subscription\Http\Requests\v1\SubscriptionCancelReason\IndexRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->passIf([])->shouldHaveResult(['view' => 'filter'])
        );
    }
}
