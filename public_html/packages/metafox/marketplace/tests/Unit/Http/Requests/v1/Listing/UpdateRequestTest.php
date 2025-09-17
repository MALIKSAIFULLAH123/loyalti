<?php

namespace MetaFox\Marketplace\Tests\Unit\Http\Requests\v1\Listing;

use MetaFox\Marketplace\Http\Requests\v1\Listing\UpdateRequest as Request;
use Tests\TestFormRequest;

class UpdateRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('title', 'categories', 'privacy', 'location'),
            $this->failIf('short_description', 1),
//            $this->failIf('price', null, 'string',),
//            $this->failIf('current_id', 1),
            $this->withSampleParameters('privacy', 'text', 'categories', 'title'),
            $this->failIf('allow_payment', 1000, 'allow_payment'),
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createNormalUser();
        $this->actingAs($this->user);
    }
}
