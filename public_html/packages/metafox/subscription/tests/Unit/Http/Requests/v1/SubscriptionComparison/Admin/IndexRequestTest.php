<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Requests\v1\SubscriptionComparison\Admin;

use MetaFox\Subscription\Http\Requests\v1\SubscriptionComparison\Admin\IndexRequest;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionComparisonAdminController::index()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class IndexRequestTest.
 */
class IndexRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return IndexRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->passIf([])->shouldHaveResult(['view' => 'admincp']),
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->asAdminUser();
    }
}
