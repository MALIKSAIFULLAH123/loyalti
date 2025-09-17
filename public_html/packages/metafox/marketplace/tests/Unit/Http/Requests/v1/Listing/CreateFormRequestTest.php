<?php

namespace MetaFox\Marketplace\Tests\Unit\Http\Requests\v1\Listing;

use MetaFox\Marketplace\Http\Requests\v1\Listing\CreateFormRequest as Request;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Marketplace\Http\Controllers\Api\v1\ListingController::createForm()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class CreateFormRequestTest.
 */
class CreateFormRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->passIf([])->shouldHaveResult(['owner_id' => 0]),
        );
    }
}
