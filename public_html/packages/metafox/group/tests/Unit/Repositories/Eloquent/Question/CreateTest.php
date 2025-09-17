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
use Prettus\Validator\Exceptions\ValidatorException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CreateTest extends TestCase
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

        return [$user, $group, $repository];
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @return array<int, mixed>
     *
     * @throws ValidatorException|AuthorizationException
     */
    public function testSuccess(array $data): array
    {
        /**
         * @var User                        $user
         * @var Group                       $group
         * @var QuestionRepositoryInterface $repository
         */
        [$user, $group, $repository] = $data;

        $params = [
            'group_id' => $group->entityId(),
            'question' => $this->faker->title,
            'type_id'  => Question::TYPE_SELECT,
            'options'  => ['new' => ['test', 'test2']],
        ];

        $question = $repository->createQuestion($user, $params);
        $this->assertNotEmpty($question);

        return [$user, $group, $repository];
    }

    /**
     * @depends testSuccess
     *
     * @param array<int, mixed> $data
     *
     * @throws ValidatorException|AuthorizationException
     */
    public function testCheckCount(array $data)
    {
        /**
         * @var User                        $user
         * @var Group                       $group
         * @var QuestionRepositoryInterface $repository
         */
        [$user, $group, $repository] = $data;

        $params = [
            'group_id' => $group->entityId(),
            'question' => $this->faker->title,
            'type_id'  => Question::TYPE_TEXT,
        ];

        $repository->createQuestion($user, $params);
        $repository->createQuestion($user, $params);

        $this->expectException(HttpException::class);
        $repository->createQuestion($user, $params);
    }
}
