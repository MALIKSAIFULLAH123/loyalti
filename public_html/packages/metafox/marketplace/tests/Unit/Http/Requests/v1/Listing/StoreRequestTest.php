<?php

namespace MetaFox\Marketplace\Tests\Unit\Http\Requests\v1\Listing;

use MetaFox\Marketplace\Http\Requests\v1\Listing\StoreRequest as Request;
use MetaFox\Marketplace\Models\Category;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Models\User;
use Tests\TestFormRequest;

class StoreRequestTest extends TestFormRequest
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

        $this->user = User::factory()->makeOne();
        $this->actingAs($this->user);
    }
}
