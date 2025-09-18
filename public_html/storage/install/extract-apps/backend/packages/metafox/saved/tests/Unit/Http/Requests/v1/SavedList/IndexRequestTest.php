<?php

namespace MetaFox\Saved\Tests\Unit\Http\Requests\v1\SavedList;

use MetaFox\Saved\Http\Requests\v1\SavedList\IndexRequest as Request;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Saved\Http\Controllers\Api\SavedListController::$controllers;
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
            $this->failIf('saved_id', 'string', []),
            $this->passIf('saved_id', 0),
            $this->withSampleParameters('page', 'limit')
        );
    }
}
