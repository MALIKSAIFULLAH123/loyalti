<?php

namespace MetaFox\Sticker\Tests\Unit\Http\Requests\v1\Sticker;

use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Sticker\Http\Controllers\Api\v1\StickerController::index;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest.
 */
class IndexRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return \MetaFox\Sticker\Http\Requests\v1\Sticker\IndexRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('set_id'),
            $this->failIf('set_id', 0, 'string'),
            $this->withSampleParameters('page', 'limit')
        );
    }
}
