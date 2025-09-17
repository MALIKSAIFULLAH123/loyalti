<?php

namespace MetaFox\Group\Tests\Unit\Rules;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Rule;
use MetaFox\Group\Rules\ConfirmRule;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ConfirmRuleTest extends TestCase
{
    public function testInstance()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $group = Group::factory()
            ->setUser($user)
            ->setPrivacyType(MetaFoxPrivacy::EVERYONE)
            ->create([
                'is_rule_confirmation' => true,
            ]);

        $this->assertInstanceOf(Group::class, $group);

        $rule = Rule::factory()
            ->create([
                'group_id'    => $group->entityId(),
                'title'       => $this->faker->title,
                'description' => $this->faker->text,
            ]);

        $this->assertInstanceOf(Rule::class, $rule);

        return [$group];
    }

    /**
     * @depends testInstance
     */
    public function testValidateSuccess(array $data)
    {
        [$group] = $data;

        $data = [
            'is_confirmed' => 1,
        ];

        $validator = Validator::make($data, [
            'is_confirmed' => [new ConfirmRule($group)],
        ]);

        $this->assertIsArray($validator->validate());
    }

    /**
     * @depends testInstance
     */
    public function testValidateFail(array $data)
    {
        [$group] = $data;

        $data = [
            'is_confirmed' => 0,
        ];

        $validator = Validator::make($data, [
            'is_confirmed' => [new ConfirmRule($group)],
        ]);

        $this->expectException(ValidationException::class);

        $validator->validate();
    }
}
