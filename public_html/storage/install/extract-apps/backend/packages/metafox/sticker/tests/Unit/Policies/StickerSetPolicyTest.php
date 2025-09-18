<?php

namespace MetaFox\Sticker\Tests\Unit\Policies;

use MetaFox\Sticker\Policies\Contracts\StickerSetPolicyInterface;
use MetaFox\Sticker\Policies\StickerSetPolicy;
use Tests\TestCase;

class StickerSetPolicyTest extends TestCase
{
    public function testInstance()
    {
        $policy = resolve(StickerSetPolicy::class);
        $this->assertInstanceOf(StickerSetPolicyInterface::class, $policy);
    }
}
