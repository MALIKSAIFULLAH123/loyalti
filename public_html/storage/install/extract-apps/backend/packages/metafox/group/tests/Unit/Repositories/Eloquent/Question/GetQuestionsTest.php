<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent\Question;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Question;
use MetaFox\Group\Repositories\Eloquent\QuestionRepository;
use MetaFox\Group\Repositories\QuestionRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class GetQuestionsTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(QuestionRepositoryInterface::class);
        $this->assertInstanceOf(QuestionRepository::class, $repository);
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $question = Question::factory()
            ->create(['type_id' => Question::TYPE_TEXT, 'group_id' => $group->entityId()]);

        return [$user, $question, $repository];
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testSuccess(array $data)
    {
        /**
         * @var User                        $user
         * @var Question                    $question
         * @var QuestionRepositoryInterface $repository
         */
        [$user, $question, $repository] = $data;

        $results = $repository->getQuestions($user, ['group_id' => $question->group_id, 'limit' => 10]);
        $this->assertTrue($results->isNotEmpty());
    }
}
