<?php

namespace MetaFox\Report\Http\Resources\v1\ReportItem;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Report\Http\Requests\v1\ReportItem\CreateFormRequest;
use MetaFox\Report\Models\ReportItem as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */
class StoreReportItemMobileForm extends StoreReportItemForm
{
    /**
     * @var Model
     */
    public $resource;

    public function boot(CreateFormRequest $request): void
    {
        $params         = $request->validated();
        $this->resource = new Model($params);
    }

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
                ->placeholder(__p('core::phrase.select'))
                ->valueType('numeric')
                ->yup(
                    Yup::number()->required()
                ),
            $this->buildTextField(),
        );
    }

    protected function buildTextField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('feedback')
                ->label(__p('report::phrase.a_comment_optional'))
                ->placeholder(__p('report::phrase.write_a_comment'));
        }

        return Builder::textArea('feedback')
            ->label(__p('report::phrase.a_comment_optional'))
            ->placeholder(__p('report::phrase.write_a_comment'));
    }
}
