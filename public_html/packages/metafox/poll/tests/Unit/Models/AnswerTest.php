<?php
/**
 * @author developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Poll\Tests\Unit\Models;

use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Poll\Models\Answer;
use MetaFox\Poll\Models\Poll;
use Tests\TestCase;

class AnswerTest extends TestCase
{
    public function makeOne($user)
    {
        /**
         * @var Poll   $model
         * @var Answer $answer
         */
        $model = Poll::factory()->setUser($user)
            ->setOwner($user)
            ->create(['privacy' => MetaFoxPrivacy::EVERYONE]);

        $answer = Answer::factory()
            ->setPoll($model)
            ->create();

        $model->refresh();

        $this->assertNotEmpty($answer->entityId());
    }
}
