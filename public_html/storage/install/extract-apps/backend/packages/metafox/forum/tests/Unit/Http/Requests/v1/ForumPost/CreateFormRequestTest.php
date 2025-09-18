<?php

namespace MetaFox\Forum\Tests\Unit\Http\Requests\v1\ForumPost;

use MetaFox\Forum\Http\Requests\v1\ForumPost\CreateFormRequest;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Forum\Http\Controllers\Api\v1\ForumPostController::createForm()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class CreateFormRequestTest.
 */
class CreateFormRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return CreateFormRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->passIf([]),
            $this->failIf('owner_id', -1, 'string'),
            $this->failIf('thread_id', -1, 'string'),
        );
    }
}
