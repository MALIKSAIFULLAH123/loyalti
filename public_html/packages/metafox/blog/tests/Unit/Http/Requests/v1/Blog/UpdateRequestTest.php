<?php

namespace MetaFox\Blog\Tests\Unit\Http\Requests\v1\Blog;

use Illuminate\Http\UploadedFile;
use MetaFox\Blog\Http\Requests\v1\Blog\UpdateRequest as Request;
use Tests\TestFormRequest;

/**
 * Class UpdateRequestTest.
 */
class UpdateRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->passIf([
                'title'      => '[phpunit sample title]',
                'categories' => [],
                'image'      => UploadedFile::fake()->image('blog.jpg'),
                'text'       => '[phpunit sample text]',
                'draft'      => 0,
                'privacy'    => 0,
            ])->withoutResult('image'),
            $this->passIf('image', UploadedFile::fake()->image('blog.jpg')),
            $this->withSampleParameters('categories', 'title', 'text', 'tags', 'privacy'),
            $this->failIf('draft', 'T', null),
            $this->passIf('draft', 1, 0),
        );
    }
}
