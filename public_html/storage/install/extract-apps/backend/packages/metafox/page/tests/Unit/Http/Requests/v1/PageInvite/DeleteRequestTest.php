<?php

namespace MetaFox\Page\Tests\Unit\Http\Requests\v1\PageInvite;

use MetaFox\Page\Http\Requests\v1\PageInvite\DeleteRequest;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Page\Http\Controllers\Api\v1\PageInviteController::delete()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class DeleteRequestTest.
 */
class DeleteRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return DeleteRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('user_id'),
            $this->passIf(['user_id' => 1])
        );
    }
}
