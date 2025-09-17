<?php

namespace MetaFox\Video\Tests\Unit\Http\Requests\v1\Category;

use MetaFox\Video\Http\Requests\v1\Category\StoreRequest;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class StoreRequestTest.
 */
class StoreRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return StoreRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->passIf([
                'name'      => 'phpunit title',
                'is_active' => 0,
                'ordering'  => 0,
            ]),
            $this->failIf('name', 0, 'A', str_pad('A', 1000, 'A')),
            $this->failIf('name_url', 0, 'A', str_pad('A', 1000, 'A')),
            $this->failIf('parent_id', 0, 'A', []),
            $this->withSampleParameters('is_active', 'ordering'),
        );
    }
}
