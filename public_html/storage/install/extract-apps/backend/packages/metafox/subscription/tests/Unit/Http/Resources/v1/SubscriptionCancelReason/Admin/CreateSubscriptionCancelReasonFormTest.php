<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Resources\v1\SubscriptionCancelReason\Admin;

use MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason\Admin\CreateSubscriptionCancelReasonForm as Form;
use MetaFox\Subscription\Models\SubscriptionCancelReason as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason\Admin\CreateForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class CreateSubscriptionCancelReasonFormTest.
 */
class CreateSubscriptionCancelReasonFormTest extends TestCase
{
    public function testCreateSubscriptionCancelReasonForm()
    {
        $this->asAdminUser();
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
