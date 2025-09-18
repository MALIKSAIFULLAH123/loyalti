<?php

namespace MetaFox\Video\Tests\Unit\Http\Resources\v1\Video;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use MetaFox\Video\Http\Resources\v1\Video\CreateVideoForm as Form;
use MetaFox\Video\Models\Video as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Video\Http\Resources\v1\Video\CreateForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class CreateVideoFormTest.
 */
class CreateVideoFormTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->assertInstanceOf(User::class, $user);

        return [$user];
    }

    /**
     * @depends testInstance
     * @param  array<int, mixed>        $data
     * @return array<int,        mixed>
     */
    public function testCreateVideoForm(array $data): array
    {
        [$user] = $data;
        $this->be($user);

        $form = new Form(null);
        $data = $form->toArray(null);
        $this->assertIsArray($data);

        return $data;
    }
}
