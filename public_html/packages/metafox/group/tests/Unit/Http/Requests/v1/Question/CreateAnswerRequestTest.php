<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Question;

use Illuminate\Routing\Redirector;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use MetaFox\Group\Http\Requests\v1\Question\StoreAnswerRequest as Request;
use MetaFox\Group\Models\Category;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Question;
use MetaFox\Group\Rules\AnswerQuestion;
use MetaFox\Group\Rules\ConfirmRule;
use MetaFox\Group\Support\Facades\Group as GroupFacade;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\UserRole;
use Tests\TestFormRequest;

class CreateAnswerRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        // todo implement test logic.
        $this->markTestIncomplete();

        return $this->makeRequests(
//            $this->shouldRequire('group_id',),
//            $this->failIf('group_id', null, 'string', 0),
        );
    }

    public function buildForm(
        $params,
        ?ConfirmRule $confirmRule = null,
        ?AnswerQuestion $answerQuestion = null
    ): Request {
        $form = new Request($params);

        $form->setContainer(app())
            ->setRedirector(app(Redirector::class));

        if ($confirmRule instanceof ConfirmRule) {
            $form->setMustConfirmRule(true);
            $form->setConfirmRule($confirmRule);
        }

        if ($answerQuestion instanceof AnswerQuestion) {
            $form->setMustAnswerMembershipQuestion(true);
            $form->setAnswerQuestionRule($answerQuestion);
        }

        return $form;
    }

    public function testInstance()
    {
        $category = Category::factory()->create();

        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create([
                'category_id'                   => $category->entityId(),
                'is_rule_confirmation'          => false,
                'is_answer_membership_question' => false,
            ]);

        $this->assertInstanceOf(Group::class, $group);

        $question = Question::factory()->create([
            'group_id' => $group->entityId(),
            'type_id'  => Question::TYPE_TEXT,
        ]);

        $this->assertInstanceOf(Question::class, $question);

        return $group;
    }

    /**
     * @depends testInstance
     */
    public function testSuccess(Group $group)
    {
        $form = $this->buildForm([
            'group_id'     => $group->id,
            'is_confirmed' => 1,
            'question'     => [
                'question_1' => $this->faker->text,
            ],
        ]);

        $form->validateResolved();

        $this->assertNotEmpty($form->validated());
    }

    /**
     * @depends testInstance
     */
    public function testFailWithConfirmationRule(Group $group)
    {
        $stub = $this->getMockBuilder(ConfirmRule::class)
            ->onlyMethods(['mustAcceptRule'])
            ->getMock();

        $stub->method('mustAcceptRule')
            ->willReturn(true);

        $this->assertTrue($stub->mustAcceptRule());

        $stub->setGroup($group);

        $form = $this->buildForm([
            'group_id'     => $group->entityId(),
            'is_confirmed' => 0,
        ], $stub);

        $this->expectException(ValidationException::class);

        $form->validateResolved();
    }

    /**
     * @depends testInstance
     */
    public function testSuccessWithConfirmationRule(Group $group)
    {
        $stub = $this->getMockBuilder(ConfirmRule::class)
            ->onlyMethods(['mustAcceptRule'])
            ->getMock();

        $stub->method('mustAcceptRule')
            ->willReturn(true);

        $this->assertTrue($stub->mustAcceptRule());

        $stub->setGroup($group);

        $form = $this->buildForm([
            'group_id'     => $group->entityId(),
            'is_confirmed' => 1,
        ], $stub);

        $form->validateResolved();

        $this->assertNotEmpty($form->validated());
    }

    /**
     * @depends testInstance
     */
    public function testFailWithAnswerQuestion(Group $group)
    {
        $stub = $this->getMockBuilder(AnswerQuestion::class)
            ->onlyMethods(['mustAnswerQuestion'])
            ->getMock();

        $stub->method('mustAnswerQuestion')
            ->willReturn(true);

        $this->assertTrue($stub->mustAnswerQuestion());

        $stub->setGroup($group);

        $form = $this->buildForm([
            'group_id' => $group->entityId(),
        ], null, $stub);

        $this->expectException(ValidationException::class);

        $form->validateResolved();
    }

    /**
     * @depends testInstance
     */
    public function testSuccessWithAnswerQuestion(Group $group)
    {
        $stub = $this->getMockBuilder(AnswerQuestion::class)
            ->onlyMethods(['mustAnswerQuestion'])
            ->getMock();

        $stub->method('mustAnswerQuestion')
            ->willReturn(true);

        $this->assertTrue($stub->mustAnswerQuestion());

        $stub->setGroup($group);

        $questions = GroupFacade::getQuestions($group);

        $this->assertInstanceOf(Collection::class, $questions);

        $questionParam = [];

        foreach ($questions as $question) {
            $questionParam['question_' . $question->entityId()] = $this->faker->text;
        }

        $form = $this->buildForm([
            'group_id' => $group->entityId(),
            'question' => $questionParam,
        ], null, $stub);

        $form->validateResolved();

        $this->assertNotEmpty($form->validated());
    }
}
