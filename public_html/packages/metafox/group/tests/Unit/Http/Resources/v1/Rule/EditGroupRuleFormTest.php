<?php

namespace MetaFox\Group\Tests\Unit\Http\Resources\v1\Rule;

use MetaFox\Group\Http\Resources\v1\Rule\UpdateGroupRuleForm as Form;
use MetaFox\Group\Models\Rule as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Group\Http\Resources\v1\Rule\EditForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class EditGroupRuleFormTest.
 */
class EditGroupRuleFormTest extends TestCase
{
    public function testEditGroupRuleForm()
    {
        $this->markTestIncomplete();
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
