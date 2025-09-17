<?php

namespace MetaFox\Group\Tests\Unit\Support;

use Illuminate\Support\Collection;
use MetaFox\Group\Contracts\SupportContract;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Question;
use MetaFox\Group\Models\Rule;
use MetaFox\Group\Support\Support;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User;
use Tests\TestCase;

class GroupSupportTest extends TestCase
{
    public function testInstance()
    {
        $support = resolve(SupportContract::class);

        $this->assertInstanceOf(Support::class, $support);

        $user = $this->createUser()->assignRole(UserRole::LEVEL_REGISTERED);

        $this->assertInstanceOf(User::class, $user);

        $group = Group::factory()
            ->setUser($user)
            ->setPrivacyType(MetaFoxPrivacy::EVERYONE)
            ->create([
                'is_rule_confirmation'          => true,
                'is_answer_membership_question' => true,
            ]);

        $this->assertInstanceOf(Group::class, $group);

        return [$support, $group];
    }

    /**
     * @depends testInstance
     */
    public function testGetGroup(array $data)
    {
        [$support, $group] = $data;

        $item = $support->getGroup($group->entityId());

        $this->assertInstanceOf(Group::class, $item);
    }

    /**
     * @depends testInstance
     */
    public function testMustAnswerMembershipQuestion(array $data)
    {
        [$support, $group] = $data;

        $question = Question::factory()
            ->create([
                'group_id' => $group->entityId(),
                'question' => $this->faker->title,
                'type_id'  => Question::TYPE_TEXT,
            ]);

        $this->assertInstanceOf(Question::class, $question);

        $result = $support->mustAnswerMembershipQuestion($group);

        $this->assertTrue($result);
    }

    /**
     * @depends testInstance
     */
    public function testMustAcceptGroupRule(array $data)
    {
        [$support, $group] = $data;

        $rule = Rule::factory()
            ->create([
                'group_id'    => $group->entityId(),
                'title'       => $this->faker->title,
                'description' => $this->faker->text,
            ]);

        $this->assertInstanceOf(Rule::class, $rule);

        $result = $support->mustAcceptGroupRule($group);

        $this->assertTrue($result);
    }

    /**
     * @depends testInstance
     */
    public function testGetQuestions(array $data)
    {
        [$support, $group] = $data;

        $result = $support->getQuestions($group);

        $this->assertInstanceOf(Collection::class, $result);
    }
}
