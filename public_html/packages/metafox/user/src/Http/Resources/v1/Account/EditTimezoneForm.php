<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\Account;

use MetaFox\Core\Support\Facades\Timezone as TimezoneFacade;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Models\User;
use MetaFox\Yup\Yup;

/**
 * Class EditTimezoneForm.
 * @property ?User $resource
 */
class EditTimezoneForm extends AbstractForm
{
    protected function prepare(): void
    {
        $timezoneId = $this->resource->profile->timezone_id;

        $value = $this->resource ? [
            'timezone_id' => $timezoneId,
        ] : null;
        $this
            ->asPut()
            ->action(url_utility()->makeApiUrl('/account/setting'))
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::choice('timezone_id')
                ->marginNormal()
                ->label(__p('core::phrase.time_zone'))
                ->placeholder(__p('core::phrase.time_zone'))
                ->required()
                ->options(TimezoneFacade::getActiveOptions())
                ->yup(Yup::string()->required()),
        );

        $footer = $this->addFooter(['separator' => false]);

        $footer->addFields(
            Builder::submit()->label(__p('core::phrase.save'))->variant('contained'),
            Builder::cancelButton()->label(__p('core::phrase.cancel'))->variant('outlined'),
        );
    }
}
