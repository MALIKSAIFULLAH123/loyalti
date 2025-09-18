<?php

namespace MetaFox\Video\Tests\Unit\Http\Requests\v1\Video;

use MetaFox\Video\Http\Requests\v1\Video\IndexRequest as Request;
use Tests\TestCase;
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
 * Class IndexRequestTest.
 */
class IndexRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->passIf([]),
            $this->withSampleParameters('q', 'view', 'sort', 'sort_type', 'when', 'category_id', 'user_id', 'page', 'limit')
        );
    }
}
