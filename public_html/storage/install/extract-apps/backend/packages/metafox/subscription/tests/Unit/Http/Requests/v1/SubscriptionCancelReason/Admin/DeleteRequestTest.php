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
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionCancelReasonAdminController::delete()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class DeleteRequestTest.
 */
class DeleteRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return \MetaFox\Subscription\Http\Requests\v1\SubscriptionCancelReason\Admin\DeleteRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('delete_option'),
            $this->failIf('delete_option', 'string', 0, 4),
            $this->passIf('delete_option', 1, 2),
            $this->failIf([
                'delete_option' => 2,
                'custom_reason' => 0,
            ], 'custom_reason')
        );
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->asAdminUser();
    }
}
