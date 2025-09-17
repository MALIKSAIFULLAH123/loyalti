<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Requests\v1\SubscriptionPackage\Admin;

use MetaFox\Subscription\Http\Requests\v1\SubscriptionPackage\Admin\ActiveRequest;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionPackageAdminController::active()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class ActiveRequestTest.
 */
class ActiveRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return ActiveRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('active'),
            $this->passIf('active', true, false, 0, 1),
        );
    }
}
