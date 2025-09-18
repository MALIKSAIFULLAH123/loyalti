<?php

namespace MetaFox\Video\Tests\Unit\Http\Resources\v1\Video;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use MetaFox\Video\Http\Resources\v1\Video\EditVideoForm as Form;
use MetaFox\Video\Models\Video as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Video\Http\Resources\v1\Video\EditForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class EditVideoFormTest.
 */
class EditVideoFormTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item = Model::factory()->setUser($user)->setOwner($user)->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(Model::class, $item);

        return [$user, $item];
    }

    /**
     * @depends testInstance
     * @param  array<int, mixed>        $data
     * @return array<int,        mixed>
     */
    public function testEditVideoForm(array $data): array
    {
        [$user, $item] = $data;
        $this->be($user);

        $form = new Form($item);
        $data = $form->toArray(null);
        $this->assertIsArray($data);

        return $data;
    }
}
