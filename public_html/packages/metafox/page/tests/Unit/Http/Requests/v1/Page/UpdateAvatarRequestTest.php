<?php

namespace MetaFox\Page\Tests\Unit\Http\Requests\v1\Page;

use Illuminate\Http\UploadedFile;
use MetaFox\Page\Http\Requests\v1\Page\UpdateAvatarRequest;
use Tests\TestCase;
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
            $this->passIf([
                'image_crop' => $this->imageBase64,
            ]),
            $this->passIf([
                'image'      => UploadedFile::fake()->image('test.jpg'),
                'image_crop' => $this->imageBase64,
            ]),
            $this->shouldRequire('image_crop'),
            $this->failIf('image_crop', 0, null, [])
        );
    }
}
