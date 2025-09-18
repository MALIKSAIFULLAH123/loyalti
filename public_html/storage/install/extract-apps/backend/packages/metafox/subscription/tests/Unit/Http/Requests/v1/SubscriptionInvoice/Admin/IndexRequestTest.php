<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Requests\v1\SubscriptionInvoice\Admin;

use MetaFox\Subscription\Http\Requests\v1\SubscriptionInvoice\Admin\IndexRequest;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionInvoiceAdminController::index()
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
        // todo risky test for payment status.
        return $this->makeRequests(
            $this->passIf([]),
            $this->passIf([
                'member_name'    => 'a',
                'id'             => 0,
                'package_id'     => 0,
                'payment_status' => 'expired',
                'limit'          => 10,
                'page'           => 2,
            ]),
            $this->withSampleParameters('page', 'limit'),
        );
    }
}
