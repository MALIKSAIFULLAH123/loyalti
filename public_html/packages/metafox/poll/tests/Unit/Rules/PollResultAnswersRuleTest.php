<?php

namespace MetaFox\Poll\Tests\Unit\Rules;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Poll\Models\Answer;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Rules\PollResultAnswersRule;
use Tests\TestCase;

class PollResultAnswersRuleTest extends TestCase
{
    /**
     * @throws ValidationException
     */
    public function testValidateSuccess(): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        /** @var Poll $poll */
        $poll = Poll::factory()->setUserAndOwner($user)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);

        /** @var Collection $answers */
        $answers = Answer::factory()->setPoll($poll)->count(4)->create();

        $data = ['answers' => [$answers->first()->entityId()]];

        $validator = Validator::make($data, [
            'answers' => [new PollResultAnswersRule($poll->entityId())],
        ]);
        $this->assertIsArray($validator->validate());
    }
}
