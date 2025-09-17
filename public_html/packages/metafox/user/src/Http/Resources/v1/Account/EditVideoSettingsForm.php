<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\Account;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\User\Models\User;
use MetaFox\User\Support\Facades\User as UserFacade;
use MetaFox\User\Support\User as UserSupport;

/**
 * Class EditVideoSettingsForm.
 * @property ?User $resource
 */
class EditVideoSettingsForm extends AbstractForm
{
    protected function prepare(): void
    {
        $values = UserFacade::getVideoSettings($this->resource);

        $this
            ->asPut()
            ->submitOnValueChanged()
            ->action('/account/setting/video/' . $this->resource->entityId())
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addField(
            Builder::switch(UserSupport::AUTO_PLAY_VIDEO_SETTING)
                ->label(__p('user::phrase.account_setting_auto_play_videos_label'))
                ->description(__p('user::phrase.account_setting_auto_play_videos_desc')),
        );
    }
}
