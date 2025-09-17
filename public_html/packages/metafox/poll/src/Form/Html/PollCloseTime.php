<?php

namespace MetaFox\Poll\Form\Html;

use Illuminate\Support\Carbon;
use MetaFox\Form\Html\Datetime;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Yup\Yup;

class PollCloseTime extends Datetime
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::DATETIME)
                ->variant('outlined')
                ->fullWidth(true)
                ->minDateTime(Carbon::now()->toISOString());

        $this->yup(Yup::date()
            ->min(Carbon::now(), __p('poll::phrase.the_close_time_should_be_greater_than_the_current_time'))
            ->nullable());
    }
}
