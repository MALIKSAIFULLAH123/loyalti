<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\Gateway\Admin;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Builder;
use MetaFox\Form\Section;
use MetaFox\Payment\Http\Resources\v1\Gateway\Admin\GatewayForm as AdminGatewayForm;
use MetaFox\Payment\Models\Gateway as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class GatewayForm.
 * @property Model $resource
 */
class GatewayForm extends AdminGatewayForm
{
    protected function handleFieldIsTest(Section $basic): AbstractField
    {
        return $basic->addField(
            Builder::hidden('is_test')
                ->label(__p('payment::phrase.is_test'))
        );
    }
}
