<?php

namespace MetaFox\Advertise\Http\Requests\v1\Advertise;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Advertise\Support\Support;
use MetaFox\Platform\Rules\AllowInRule;

class ReportRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'report_type'                                 => ['required', 'string', new AllowInRule([Support::TYPE_CLICK, Support::TYPE_IMPRESSION])],
            'view'                                        => ['required', 'string', new AllowInRule([Support::STATISTIC_VIEW_WEEK, Support::STATISTIC_VIEW_MONTH, Support::STATISTIC_VIEW_DAY])],
            'start_date_' . Support::STATISTIC_VIEW_MONTH => ['nullable', 'string'],
            'end_date_' . Support::STATISTIC_VIEW_MONTH   => ['nullable', 'string'],
            'start_date_' . Support::STATISTIC_VIEW_WEEK  => ['nullable', 'string'],
            'end_date_' . Support::STATISTIC_VIEW_WEEK    => ['nullable', 'string'],
            'start_date_' . Support::STATISTIC_VIEW_DAY   => ['nullable', 'string'],
            'end_date_' . Support::STATISTIC_VIEW_DAY     => ['nullable', 'string'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $view = Arr::get($data, 'view');

        switch ($view) {
            case Support::STATISTIC_VIEW_WEEK:
                Arr::set($data, 'date', [
                    'start' => Arr::get($data, 'start_date_' . Support::STATISTIC_VIEW_WEEK),
                    'end'   => Arr::get($data, 'end_date_' . Support::STATISTIC_VIEW_WEEK),
                ]);

                unset($data['start_date_' . Support::STATISTIC_VIEW_WEEK]);
                unset($data['end_date_' . Support::STATISTIC_VIEW_WEEK]);

                break;
            case Support::STATISTIC_VIEW_DAY:
                Arr::set($data, 'date', [
                    'start' => Arr::get($data, 'start_date_' . Support::STATISTIC_VIEW_DAY),
                    'end'   => Arr::get($data, 'end_date_' . Support::STATISTIC_VIEW_DAY),
                ]);

                unset($data['start_date_' . Support::STATISTIC_VIEW_DAY]);
                unset($data['end_date_' . Support::STATISTIC_VIEW_DAY]);

                break;
            default:
                Arr::set($data, 'date', [
                    'start' => Arr::get($data, 'start_date_' . Support::STATISTIC_VIEW_MONTH),
                    'end'   => Arr::get($data, 'end_date_' . Support::STATISTIC_VIEW_MONTH),
                ]);

                unset($data['start_date_' . Support::STATISTIC_VIEW_MONTH]);
                unset($data['end_date_' . Support::STATISTIC_VIEW_MONTH]);
        }

        return $data;
    }
}
