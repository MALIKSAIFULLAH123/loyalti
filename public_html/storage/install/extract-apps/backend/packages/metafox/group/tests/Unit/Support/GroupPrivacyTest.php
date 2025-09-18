<?php

namespace MetaFox\Group\Tests\Unit\Support;

use MetaFox\Group\Support\GroupPrivacy as GroupPrivacySupport;
use MetaFox\User\Contracts\Support\PrivacyForSettingInterface;
use Tests\TestCase;

class GroupPrivacyTest extends TestCase
{
    public function testInstance()
    {
        $this->markTestIncomplete();
        $this->assertInstanceOf(PrivacyForSettingInterface::class, GroupPrivacySupport::class);
    }
}
