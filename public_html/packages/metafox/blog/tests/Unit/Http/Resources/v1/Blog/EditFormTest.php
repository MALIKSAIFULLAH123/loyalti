<?php

namespace MetaFox\Blog\Tests\Unit\Http\Resources\v1\Blog;

use MetaFox\Blog\Http\Resources\v1\Blog\UpdateBlogForm as Form;
use MetaFox\Blog\Models\Blog as Model;
use MetaFox\Form\AbstractForm;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Blog\Http\Resources\v1\Blog\UpdateBlogForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class EditFormTest.
 */
class EditFormTest extends TestCase
{
    public function testEditForm()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->be($user);
        $blog = Model::factory()->setUser($user)->setOwner($user)->create();
        $form = new Form($blog);
        $this->assertInstanceOf(AbstractForm::class, $form);
    }
}
