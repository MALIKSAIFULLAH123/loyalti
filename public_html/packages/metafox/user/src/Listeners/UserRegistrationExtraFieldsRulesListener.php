<?php

namespace MetaFox\User\Listeners;

use ArrayObject;
use MetaFox\Core\Rules\Base64FileTypeRule;
use MetaFox\Platform\Facades\Settings;

class UserRegistrationExtraFieldsRulesListener
{
    /**
     * @param  ArrayObject $rules
     * @return void
     */
    public function handle(ArrayObject $rules): void
    {
        if (!Settings::get('user.force_user_to_upload_on_sign_up', false)) {
            return;
        }

        $rules['user_profile']        = ['required', 'array'];
        $rules['user_profile.base64'] = ['required', 'string', new Base64FileTypeRule('photo')];
    }
}
