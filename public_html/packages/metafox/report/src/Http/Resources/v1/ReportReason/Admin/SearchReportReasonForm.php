<?php

namespace MetaFox\Report\Http\Resources\v1\ReportReason\Admin;

use MetaFox\Form\Html\BuiltinAdminSearchForm;
use MetaFox\Report\Models\ReportReason as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchReportReasonForm.
 * @property Model $resource
 */
class SearchReportReasonForm extends BuiltinAdminSearchForm
{
    protected function prepare(): void
    {
        $this->action('/report/reason')
            ->acceptPageParams(['q']);
    }
}
