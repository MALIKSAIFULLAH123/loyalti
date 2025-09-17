<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Group;

use MetaFox\Group\Http\Requests\v1\Group\UpdateAvatarRequest;
use Tests\TestFormRequest;

/**
 * Class UpdateAvatarRequestTest.
 */
class UpdateAvatarRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return UpdateAvatarRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('image'),
            $this->failIf('image', 0, null), // todo risk with image="any string"
            $this->passIf('image', $this->imageBase64)
        );
    }
}
