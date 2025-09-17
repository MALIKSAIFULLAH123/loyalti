<?php

namespace MetaFox\Announcement\Tests\Unit\Http\Requests\v1\Announcement;

use MetaFox\Announcement\Http\Requests\v1\Announcement\IndexRequest;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Announcement\Http\Controllers\Api\AnnouncementController::$controllers;
 * stub: api_action_request_test.stub
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
            $this->passIf([]),
            $this->withSampleParameters('limit'),
        );
    }
}
