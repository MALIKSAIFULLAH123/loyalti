<?php

namespace MetaFox\Saved\Tests\Unit\Http\Requests\v1\SavedList;

use MetaFox\Saved\Http\Requests\v1\SavedList\StoreRequest as Request;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Saved\Http\Controllers\Api\SavedListController::$controllers
 * stub: api_action_request_test.stub
 */

/**
 * Class StoreRequestTest.
 */
class StoreRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('name', 'privacy'),
            $this->withSampleParameters('privacy'),
            $this->failIf('name', 0, null, [], str_pad('A', 500, 'A')),
        );
    }
}
