<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Mute;

use MetaFox\Group\Http\Requests\v1\Mute\UpdateRequest;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Group\Http\Controllers\Api\v1\MuteController::update()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class UpdateRequestTest.
 */
class UpdateRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return UpdateRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->passIf([])
        );
    }
}
