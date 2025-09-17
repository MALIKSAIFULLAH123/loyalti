<?php

namespace MetaFox\Report\Http\Resources\v1\ReportItem;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Report\Models\ReportItem as Model;
use MetaFox\Report\Models\ReportReason;
use MetaFox\Report\Repositories\ReportReasonRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreReportItemForm.
 */
class StoreReportItemForm extends AbstractForm
{
    /**
     * @var Model
     */
    public $resource;

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $termOfUserLink = 'https://preview-metafox.phpfox.us/backend/terms/';
        $reason         = $this->getReportDefault();

        $this->title(__p('report::phrase.report_title'))
            ->action(url_utility()->makeApiUrl('report'))
            ->asPost()
            ->description(__p('report::phrase.you_are_about_to_report_a_violation', ['link' => $termOfUserLink]))
            ->setValue([
                'item_id'   => $this->resource->item_id,
                'item_type' => $this->resource->item_type,
                'reason'    => $reason->entityId(),
                'feedback'  => '',
            ]);
    }

    /**
     * @throws AuthenticationException
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::hidden('item_id')
                ->required(),
            Builder::hidden('item_type')
                ->required(),
            Builder::choice('reason')
                ->required()
                ->options($this->getReportReason())
                ->label(__p('user::phrase.reason'))
                ->valueType('numeric'),
            Builder::textArea('feedback')
                ->label(__p('report::phrase.a_comment_optional'))
                ->placeholder(__p('report::phrase.write_a_comment'))
        );

        $this->addFooter()
            ->addFields(
                Builder::submit('submit')
                    ->label(__p('core::phrase.submit'))
            );
    }

    /**
     * @return array<int,              mixed>
     * @throws AuthenticationException
     */
    protected function getReportReason(): array
    {
        $reasons = $this->reasonRepository()->getFormReason(user());

        return $reasons->map(function (ReportReason $reason) {
            return [
                'label' => $reason->name,
                'value' => $reason->entityId(),
            ];
        })->toArray();
    }

    /**
     * @return ReportReason
     * @throws AuthenticationException
     */
    protected function getReportDefault(): ReportReason
    {
        $reasons = $this->reasonRepository()->getFormReason(user());

        return $reasons->filter(function (ReportReason $reason) {
            return $reason->is_default;
        })->first();
    }

    protected function reasonRepository(): ReportReasonRepositoryInterface
    {
        return resolve(ReportReasonRepositoryInterface::class);
    }
}
