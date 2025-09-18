<?php

namespace MetaFox\Sticker\Tests\Unit\Http\Requests\v1\Sticker;

use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Sticker\Http\Controllers\Api\v1\StickerController::createRecent;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class CreateRecentRequest.
 */
class CreateRecentRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return \MetaFox\Sticker\Http\Requests\v1\Sticker\CreateRecentRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('sticker_id'),
            $this->failIf('sticker_id', 0, 'string')
        );
    }
}
