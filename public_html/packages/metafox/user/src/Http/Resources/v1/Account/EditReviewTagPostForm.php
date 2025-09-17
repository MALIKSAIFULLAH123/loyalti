<?php

namespace MetaFox\User\Http\Resources\v1\Account;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Support\Facades\UserValue;
use MetaFox\User\Support\User;

/**
 * Class EditReviewTagPostForm.
 * @property ?Model $resource
 */
class EditReviewTagPostForm extends AbstractForm
{
    public function prepare(): void
    {
        $settingName = 'user_auto_add_tagger_post';
        $value       = User::AUTO_APPROVED_TAGGER_POST;
        if (isset($this->resource)) {
            $value = UserValue::getUserValueSettingByName(
                $this->resource,
                $settingName
            ) ?? User::AUTO_APPROVED_TAGGER_POST;
        }
        $this->action(url_utility()
            ->makeApiUrl('/account/profile-privacy'))
            ->asPut()
            ->submitOnValueChanged()
            ->setValue([
                'user_auto_add_tagger_post' => $value,
            ]);
    }

    public function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addField(
            Builder::switch('user_auto_add_tagger_post')
                ->label(__p('user::phrase.auto_add_tagger_posts_on_your_timeline'))
                ->setAttribute('styleGroup', 'review_post')
        );
    }
}
