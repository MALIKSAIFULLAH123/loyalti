<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent\Question;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Question;
use MetaFox\Group\Models\QuestionField;
use MetaFox\Group\Repositories\Eloquent\QuestionRepository;
use MetaFox\Group\Repositories\QuestionRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class UpdateTest extends TestCase
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
     * @return array<int, mixed>
     *
     * @throws AuthorizationException|ValidationException
     */
    public function testSuccess(array $data): array
    {
        /**
         * @var User                        $user
         * @var Question                    $question
         * @var QuestionRepositoryInterface $repository
         */
        [$user, $question, $repository] = $data;

        $title  = $this->faker->title;
        $params = [
            'question' => $title,
        ];

        $repository->updateQuestion($user, $question->entityId(), $params);
        $this->assertSame($title, $question->refresh()->question);

        return [$user, $question, $repository];
    }

    /**
     * @depends testSuccess
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException|ValidationException
     */
    public function testUpdateWithTypeSelectError(array $data)
    {
        /**
         * @var User                        $user
         * @var Question                    $question
         * @var QuestionRepositoryInterface $repository
         */
        [$user, $question, $repository] = $data;

        $params = [
            'type_id' => Question::TYPE_SELECT,
        ];

        $this->expectException(ValidationException::class);
        $repository->updateQuestion($user, $question->entityId(), $params);
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @return array<int, mixed>
     *
     * @throws AuthorizationException|ValidationException
     */
    public function testUpdateWithTypeSelect(array $data): array
    {
        /**
         * @var User                        $user
         * @var Question                    $question
         * @var QuestionRepositoryInterface $repository
         */
        [$user, $question, $repository] = $data;

        $params = [
            'type_id' => Question::TYPE_SELECT,
            'options' => ['new' => ['test', 'test 2', 'test 3']],
        ];

        $repository->updateQuestion($user, $question->entityId(), $params);
        $this->assertTrue($question->refresh()->questionFields->isNotEmpty());

        return [$user, $question, $repository];
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @return array<int, mixed>
     *
     * @throws AuthorizationException|ValidationException
     */
    public function testUpdateOptions(array $data): array
    {
        /**
         * @var User                        $user
         * @var Question                    $question
         * @var QuestionRepositoryInterface $repository
         */
        [$user, $question, $repository] = $data;

        $fieldIds = $question->questionFields->pluck('id')->toArray();

        $checkCount = $question->questionFields->count();
        $title      = $this->faker->title;
        $params     = [
            'options' => [
                'update' => [
                    ['id' => $fieldIds[1], 'title' => $title],
                ],
                'remove' => [$fieldIds[2]],
            ],
        ];
        $this->markTestIncomplete();
        $repository->updateQuestion($user, $question->entityId(), $params);
        $this->assertTrue($question->refresh()->questionFields->count() == ($checkCount - 1));
        /** @var QuestionField $field2 */
        $field2 = QuestionField::query()->findOrFail($fieldIds[1]);
        $this->assertTrue($title == $field2->title);

        return [$user, $question, $repository];
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException|ValidationException
     */
    public function testUpdateWithTypeText(array $data)
    {
        /**
         * @var User                        $user
         * @var Question                    $question
         * @var QuestionRepositoryInterface $repository
         */
        [$user, $question, $repository] = $data;

        $params = [
            'type_id' => Question::TYPE_TEXT,
        ];

        $repository->updateQuestion($user, $question->entityId(), $params);
        $this->assertTrue($question->refresh()->questionFields->isEmpty());
    }
}
