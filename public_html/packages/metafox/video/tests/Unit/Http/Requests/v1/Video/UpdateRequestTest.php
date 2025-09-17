<?php

namespace MetaFox\Video\Tests\Unit\Http\Requests\v1\Video;

use MetaFox\Platform\Facades\Settings;
use MetaFox\Video\Http\Requests\v1\Video\UpdateRequest;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Video\Http\Controllers\Api\VideoController::$controllers;
 * stub: api_action_request_test.stub
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

    protected function setUp(): void
    {
        parent::setUp();

        Settings::mockValues([
            'video.minimum_name_length' => 5,
            'video.maximum_name_length' => 100,
        ]);
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->passIf([
                'text'    => 'sample text',
                'title'   => 'sample title',
                'privacy' => 0,
            ]),
            $this->failIf('title', 0, 'A', str_pad('A', 1000, 'A')),
            $this->withSampleParameters('categories', 'privacy', 'owner_id'),
        );
    }
}
