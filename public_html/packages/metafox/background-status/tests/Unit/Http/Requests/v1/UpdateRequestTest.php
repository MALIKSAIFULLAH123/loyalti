<?php

namespace MetaFox\BackgroundStatus\Tests\Unit\Http\Requests\v1;

use MetaFox\BackgroundStatus\Http\Requests\v1\UpdateRequest;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class UpdateRequestTest.
 */
class UpdateRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return UpdateRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->passIf([
                'title'                => fake()->title,
                'is_active'            => 1,
                'is_default'           => 1,
                'background_temp_file' => [1],
            ]),
            $this->withSampleParameters('is_active', 'is_default')
        );
    }
}
