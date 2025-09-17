<?php

namespace MetaFox\Report\Http\Resources\v1\ReportReason\Admin;

use MetaFox\Core\Support\Facades\Language;
use MetaFox\Report\Models\ReportReason as Model;
use MetaFox\Report\Repositories\ReportReasonRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditReportReasonForm.
 * @property Model $resource
 */
class EditReportReasonForm extends CreateReportReasonForm
{
    protected ReportReasonRepositoryInterface $repository;

    public function boot(ReportReasonRepositoryInterface $repository, ?int $id = null): void
    {
        $this->repository = $repository;
        $this->resource   = $this->repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->action('/admincp/report/reason/' . $this->resource?->id)
            ->asPut()
            ->setValue([
                'name' => Language::getPhraseValues($this->resource->getRawOriginal('name', '')),
            ]);
    }
}
