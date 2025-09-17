<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Mute;

use MetaFox\Group\Http\Requests\v1\Mute\IndexRequest;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Group\Http\Controllers\Api\v1\MuteController::index()
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
            $this->shouldRequire('group_id'),
            $this->failIf('group_id', 'string', 0, null),
            $this->withSampleParameters('limit'),
        );
    }
}
