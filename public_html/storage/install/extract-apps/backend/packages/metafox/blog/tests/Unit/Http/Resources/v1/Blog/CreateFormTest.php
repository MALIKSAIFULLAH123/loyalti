<?php

namespace MetaFox\Blog\Tests\Unit\Http\Resources\v1\Blog;

use MetaFox\Blog\Http\Resources\v1\Blog\StoreBlogForm as Form;
use MetaFox\Blog\Models\Blog as Model;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Blog\Http\Resources\v1\Blog\StoreBlogForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class CreateFormTest.
 */
class CreateFormTest extends TestCase
{
    public function testCreateForm()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->be($user);

        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
