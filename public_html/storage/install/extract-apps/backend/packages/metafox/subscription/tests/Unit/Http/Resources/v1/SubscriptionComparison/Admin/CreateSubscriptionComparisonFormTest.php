<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Resources\v1\SubscriptionComparison\Admin;

use MetaFox\Subscription\Http\Resources\v1\SubscriptionComparison\Admin\CreateSubscriptionComparisonForm as Form;
use MetaFox\Subscription\Models\SubscriptionComparison as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Subscription\Http\Resources\v1\SubscriptionComparison\Admin\CreateForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class CreateSubscriptionComparisonFormTest.
 */
class CreateSubscriptionComparisonFormTest extends TestCase
{
    public function testCreateSubscriptionComparisonForm()
    {
        $this->asAdminUser();
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
