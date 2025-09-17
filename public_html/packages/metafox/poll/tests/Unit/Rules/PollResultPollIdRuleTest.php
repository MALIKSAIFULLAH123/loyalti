<?php

namespace MetaFox\Poll\Tests\Unit\Rules;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Rules\PollResultPollIdRule;
use Tests\TestCase;

class PollResultPollIdRuleTest extends TestCase
{
    /**
     * @throws ValidationException
     */
    public function testValidateSuccess(): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        /** @var Poll $poll */
        $poll = Poll::factory()->setUserAndOwner($user)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);

        $data      = ['poll_id' => $poll->entityId()];
        $validator = Validator::make($data, [
            'poll_id' => [new PollResultPollIdRule()],
        ]);
        $this->assertIsArray($validator->validate());
    }
}
