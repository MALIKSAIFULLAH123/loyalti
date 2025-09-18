<?php

namespace MetaFox\Video\Tests\Unit\Models;

use MetaFox\Video\Models\PrivacyStream;
use Tests\TestCase;
use Tests\TestCases\TestPrivacyStreamModel;

class PrivacyStreamTest extends TestPrivacyStreamModel
{
    public function modelName(): string
    {
        return PrivacyStream::class;
    }
}
