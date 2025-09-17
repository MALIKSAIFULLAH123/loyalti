<?php

namespace MetaFox\Mobile\Http\Requests\v1\SiteSettingForm\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Mobile\Facades\Mobile;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Mobile\Http\Controllers\Api\v1\AdMobConfigAdminController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateSiteSettingRequest.
 */
class UpdateSiteSettingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'mobile.smart_banner.position'       => ['required', 'string', new AllowInRule(Mobile::getAllowSmartBannerPosition())],
            'mobile.smart_banner.icon'           => ['sometimes', 'nullable', 'array'],
            'mobile.smart_banner.icon.temp_file' => ['required_if:mobile.smart_banner.icon,create,update', 'numeric', new ExistIfGreaterThanZero('exists:storage_files,id')],
        ];
    }

    public function validated($key = null, $default = null)
    {
        return parent::validated($key, $default);
    }
}
