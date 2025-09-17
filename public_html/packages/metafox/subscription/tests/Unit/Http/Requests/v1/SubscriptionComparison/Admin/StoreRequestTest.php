<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Requests\v1\SubscriptionComparison\Admin;

use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionComparisonAdminController::store()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class StoreRequestTest.
 */
class StoreRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return \MetaFox\Subscription\Http\Requests\v1\SubscriptionComparison\Admin\StoreRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('title'),
            $this->failIf('title', '', null, 0, str_pad('A', 500, 'A')),
        );
    }

    public function testAddPackageRule()
    {
        // todo: implement package rule
        $this->markTestIncomplete();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->asAdminUser();
    }
}
