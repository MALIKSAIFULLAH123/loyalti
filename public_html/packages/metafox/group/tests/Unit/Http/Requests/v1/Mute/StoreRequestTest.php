<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Mute;

use MetaFox\Group\Http\Requests\v1\Mute\StoreRequest as Request;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Group\Http\Controllers\Api\v1\MuteController::store()
 * stub: /packages/requests/api_action_request_test.stub
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
            $this->shouldRequire('group_id', 'user_id'),
            $this->failIf('group_id', 0, null, 'string'),
            $this->failIf('user_id', 0, null, 'string'),
        );
    }
}
