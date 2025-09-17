<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Question;

use Illuminate\Validation\ValidationException;
use MetaFox\Group\Http\Requests\v1\Question\StoreRequest as Request;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Question;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\UserRole;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class StoreRequestTest.
 */
class StoreRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('group_id', 'question', 'type_id'),
            $this->failIf('group_id', 0, null, 'string'),
            $this->failIf('type_id', null, 'string', -1, 3),
            $this->passIf('type_id', 0, 1, 2),
            $this->passIf('question', 'any string '),
        );
    }

    public function testSuccess(): Group
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $group = Group::factory()->setUser($user)->setPrivacyType(PrivacyTypeHandler::PUBLIC)->create();
        $form  = $this->buildForm([
            'group_id' => $group->entityId(),
            'question' => $this->faker->title,
            'type_id'  => Question::TYPE_TEXT,
        ]);

        $form->validateResolved();
        $this->assertNotEmpty($form->validated());

        return $group;
    }

    /**
     * @depends testSuccess
     */
    public function testOptionsArray(Group $group)
    {
        $form = $this->buildForm([
            'group_id' => $group->entityId(),
            'question' => $this->faker->title,
            'type_id'  => Question::TYPE_SELECT,
            'options'  => 'test',
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    /**
     * @depends testSuccess
     */
    public function testOptionsNewArray(Group $group)
    {
        $form = $this->buildForm([
            'group_id' => $group->entityId(),
            'question' => $this->faker->title,
            'type_id'  => Question::TYPE_SELECT,
            'options'  => [
                'new' => 'test',
            ],
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    /**
     * @depends testSuccess
     */
    public function testOptionsNewArrayString(Group $group)
    {
        $form = $this->buildForm([
            'group_id' => $group->entityId(),
            'question' => $this->faker->title,
            'type_id'  => Question::TYPE_SELECT,
            'options'  => [
                'new' => [1],
            ],
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }
}
