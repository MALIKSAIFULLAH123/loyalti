<?php

namespace MetaFox\Sticker\Tests\Unit\Http\Requests\v1\StickerSet;

use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class UpdateRequestTest.
 */
class UpdateRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return \MetaFox\Sticker\Http\Requests\v1\StickerSet\UpdateRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->passIf([]),
            $this->passIf([
                'title'             => fake()->words(3, true),
                'sticker_temp_file' => [1],
            ])
        );
    }
}
