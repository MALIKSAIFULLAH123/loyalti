<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Resources\v1\SubscriptionInvoice;

use MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\CancelSubscriptionInvoiceForm as Form;
use MetaFox\Subscription\Models\SubscriptionInvoice as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\CancelForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class CancelSubscriptionInvoiceFormTest.
 */
class CancelSubscriptionInvoiceFormTest extends TestCase
{
    public function testCancelSubscriptionInvoiceForm()
    {
        $this->asAdminUser();
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
