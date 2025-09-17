<?php

namespace MetaFox\ChatPlus\Tests\Unit\Models;

use MetaFox\ChatPlus\Models\Job;
use Tests\TestCase;

class JobTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreateInstance()
    {
        /** @var Job $job */
        $job = Job::factory()->makeOne([
            'name'    => __CLASS__,
            'data'    => ['from' => __METHOD__],
            'is_sent' => 1,
        ]);

        $this->assertTrue($job->saveQuietly());

        $job->refresh();

        $this->assertSame(__METHOD__, $job->data['from']);
        $this->assertSame(__CLASS__, $job->name);
        $this->assertSame(1, $job->is_sent);
    }
}

// end
