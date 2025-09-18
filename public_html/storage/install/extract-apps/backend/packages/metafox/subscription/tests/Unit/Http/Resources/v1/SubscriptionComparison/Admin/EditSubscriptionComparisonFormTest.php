<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Resources\v1\SubscriptionComparison\Admin;

use MetaFox\Subscription\Http\Resources\v1\SubscriptionComparison\Admin\EditSubscriptionComparisonForm as Form;
use MetaFox\Subscription\Models\SubscriptionComparison as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Subscription\Http\Resources\v1\SubscriptionComparison\Admin\EditForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class EditSubscriptionComparisonFormTest.
 */
class EditSubscriptionComparisonFormTest extends TestCase
{
    public function testEditSubscriptionComparisonForm()
    {
        $this->asAdminUser();
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
