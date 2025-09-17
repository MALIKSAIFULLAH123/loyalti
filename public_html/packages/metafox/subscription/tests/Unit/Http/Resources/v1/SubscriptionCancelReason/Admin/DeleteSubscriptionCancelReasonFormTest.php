<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Resources\v1\SubscriptionCancelReason\Admin;

use MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason\Admin\DeleteSubscriptionCancelReasonForm as Form;
use MetaFox\Subscription\Models\SubscriptionCancelReason as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason\Admin\DeleteForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class DeleteSubscriptionCancelReasonFormTest.
 */
class DeleteSubscriptionCancelReasonFormTest extends TestCase
{
    public function testDeleteSubscriptionCancelReasonForm()
    {
        $this->asAdminUser();
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
