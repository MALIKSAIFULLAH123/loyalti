<?php

namespace MetaFox\Page\Tests\Unit\Http\Requests\v1\Page;

use Illuminate\Http\UploadedFile;
use MetaFox\Page\Http\Requests\v1\Page\UpdateCoverRequest as Request;
use Tests\TestCase;
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
            $this->passIf(['image' => UploadedFile::fake()->image('cover.jpg')]),
            $this->failIf('image', UploadedFile::fake()->create('cover', 10, 'application/pdf')),
            $this->failIf('position', 0, null)
        );
    }
}
