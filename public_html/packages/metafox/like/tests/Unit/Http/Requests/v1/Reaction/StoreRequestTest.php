<?php

namespace MetaFox\Like\Tests\Unit\Http\Requests\v1\Reaction;

use Illuminate\Http\UploadedFile;
use MetaFox\Like\Http\Requests\v1\Reaction\StoreRequest as Request;
use Tests\TestFormRequest;

/**
 * Class StoreRequestTest.
 */
class StoreRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->passIf([
                'title'     => 'sample title',
                'icon'      => UploadedFile::fake()->image('sample.jpg', 100, 100),
                'color'     => '#FAFAFA',
                'ordering'  => 0,
                'is_active' => 1,
            ]),
            $this->failIf('title', 0, null),
            $this->failIf('icon', 'string', 0)
        );
    }
}
