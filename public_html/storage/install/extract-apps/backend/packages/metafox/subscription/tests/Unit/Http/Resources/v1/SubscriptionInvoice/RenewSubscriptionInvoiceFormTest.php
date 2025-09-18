<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Resources\v1\SubscriptionInvoice;

use MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\RenewSubscriptionInvoiceForm as Form;
use MetaFox\Subscription\Models\SubscriptionInvoice as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\RenewForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class RenewSubscriptionInvoiceFormTest.
 */
class RenewSubscriptionInvoiceFormTest extends TestCase
{
    public function testRenewSubscriptionInvoiceForm()
    {
        $this->markTestIncomplete();
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
