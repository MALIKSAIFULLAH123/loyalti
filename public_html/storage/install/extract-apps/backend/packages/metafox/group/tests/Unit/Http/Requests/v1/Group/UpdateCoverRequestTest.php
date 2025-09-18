<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Group;

use Illuminate\Http\UploadedFile;
use MetaFox\Group\Http\Requests\v1\Group\UpdateCoverRequest as Request;
use Tests\TestFormRequest;

/**
 * Class UpdateCoverRequestTest.
 */
class UpdateCoverRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->passIf([
                'image' => UploadedFile::fake()->image('cover.jpg'),
            ]),
            $this->failIf([
                'image' => UploadedFile::fake()->create('cover', 10, 'application/pdf'),
            ], 'image'),
            $this->failIf('position', 0, null)
        );
    }
}
