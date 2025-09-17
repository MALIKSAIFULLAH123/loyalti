<?php

namespace MetaFox\Group\Tests\Unit\Http\Resources\v1\Rule;

use MetaFox\Group\Http\Resources\v1\Rule\StoreGroupRuleForm as Form;
use MetaFox\Group\Models\Rule as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Group\Http\Resources\v1\Rule\CreateForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class CreateGroupRuleFormTest.
 */
class CreateGroupRuleFormTest extends TestCase
{
    public function testCreateGroupRuleForm()
    {
        $this->markTestIncomplete();
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
