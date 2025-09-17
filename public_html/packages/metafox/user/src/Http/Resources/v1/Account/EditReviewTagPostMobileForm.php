<?php

namespace MetaFox\User\Http\Resources\v1\Account;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Platform\Contracts\User as UserContract;
use MetaFox\User\Support\Facades\UserValue;
use MetaFox\User\Support\User;

/**
 * Class EditReviewTagPostMobileForm.
 * @property UserContract $resource
 * @driverName user.account.review_tag
 */
class EditReviewTagPostMobileForm extends AbstractForm
{
    /**
     * @throws AuthenticationException
     */
    public function boot(): void
    {
        $this->resource = user();
    }

    public function prepare(): void
    {
        $settingName = User::AUTO_APPROVED_TAGGED_SETTING;

        $this->action(url_utility()
            ->makeApiUrl('/account/profile-privacy'))
            ->asPut()
            ->submitOnValueChanged()
            ->setValue([
                $settingName => UserValue::getUserValueSettingByName($this->resource, $settingName) ?? 0,
            ]);
    }

    public function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addField(
            Builder::switch(User::AUTO_APPROVED_TAGGED_SETTING)
                ->marginNone()
                ->label(__p('user::phrase.auto_add_tagger_posts_on_your_timeline'))
        );
    }
}
