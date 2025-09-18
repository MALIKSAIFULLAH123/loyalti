<?php

namespace MetaFox\Page\Tests\Unit\Http\Resources\v1\Page;

use MetaFox\Page\Http\Resources\v1\Page\ClaimPageForm as Form;
use MetaFox\Page\Models\Page as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Page\Http\Resources\v1\Page\ClaimForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class ClaimPageFormTest.
 */
class ClaimPageFormTest extends TestCase
{
    public function testClaimPageForm()
    {
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
