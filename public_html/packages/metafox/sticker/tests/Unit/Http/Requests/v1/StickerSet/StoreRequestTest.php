<?php

namespace MetaFox\Sticker\Tests\Unit\Http\Requests\v1\StickerSet;

use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class StoreRequestTest.
 */
class StoreRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return \MetaFox\Sticker\Http\Requests\v1\StickerSet\StoreRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->passIf([
                'title' => fake()->words(3, true),
            ]),
            $this->passIf([
                'title'             => fake()->words(3, true),
                'is_active'         => 1,
                'sticker_temp_file' => [1],
            ])
        );
    }
}
