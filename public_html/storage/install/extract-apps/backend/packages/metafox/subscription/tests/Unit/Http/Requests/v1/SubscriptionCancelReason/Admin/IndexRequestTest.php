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
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionCancelReasonAdminController::index()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class IndexRequestTest.
 */
class IndexRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return \MetaFox\Subscription\Http\Requests\v1\SubscriptionCancelReason\Admin\IndexRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->passIf([
                'view'      => 'any string',
                'statistic' => 'all',
            ])->shouldHaveResult(['view' => 'admincp', 'statistic' => 'all']),
        );
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->asAdminUser();
    }
}
