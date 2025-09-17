<?php

namespace MetaFox\Announcement\Tests\Unit\Http\Requests\v1\Announcement;

use MetaFox\Announcement\Http\Requests\v1\Announcement\HideRequest as Request;
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
class HideRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('announcement_id')
        );
    }

    protected function beforeTest()
    {
        $user = $this->createNormalUser();
        $this->be($user);
    }
}
