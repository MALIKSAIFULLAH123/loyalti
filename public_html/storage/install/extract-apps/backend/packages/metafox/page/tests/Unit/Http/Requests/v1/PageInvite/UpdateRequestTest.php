<?php

namespace MetaFox\Page\Tests\Unit\Http\Requests\v1\PageInvite;

use MetaFox\Page\Http\Requests\v1\PageInvite\UpdateRequest;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Page\Http\Controllers\Api\v1\PageInviteController::update()
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
            $this->shouldRequire( 'accept'),
            $this->failIf('accept', null, 'string'),
            $this->passIf('accept', 0, 1),
        );
    }
}
