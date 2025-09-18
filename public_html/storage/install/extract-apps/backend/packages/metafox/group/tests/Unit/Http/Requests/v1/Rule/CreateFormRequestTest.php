<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Rule;

use MetaFox\Group\Http\Requests\v1\Rule\CreateFormRequest as Request;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Group\Http\Controllers\Api\v1\RuleController::createForm()
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
            $this->shouldRequire('group_id'),
            $this->failIf('group_id', null, 'string', 0, [])
        );
    }
}
