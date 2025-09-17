<?php

namespace MetaFox\Core\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MetaFox\App\Support\MetaFoxStore;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\PackageManager;
use MetaFox\Yup\Yup;

/**
 * Class LicenseKeySettingForm.
 */
class LicenseKeySettingForm extends Form
{
    protected function prepare(): void
    {
        $licenseId = Settings::get('core.license.id');

        $values = [];
        Arr::set($values, 'core.license.id', $licenseId);

        $this->title(__p('core::phrase.license_settings'))
            ->action('admincp/setting/core/license-key')
            ->asPost()
            ->setValue($values);
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $licenseKey     = Settings::get('core.license.key');
        $keyLength      = mb_strlen($licenseKey);
        $licenseKeyDesc = __p(
            'core::phrase.license_key_desc',
            ['key' => Str::mask($licenseKey, '*', -$keyLength, $keyLength - 4)]
        );

        $basic->addFields(
            Builder::text('core.license.id')
                ->required(true)
                ->variant('outlined')
                ->autoComplete('off')
                ->label(__p('core::phrase.license_id'))
                ->yup(Yup::string()->required(__p('validation.this_field_is_a_required_field'))),
            Builder::text('core.license.key')
                ->label(__p('core::phrase.new_license_key'))
                ->description($licenseKeyDesc)
                ->autoComplete('off')
                ->yup(Yup::string()->required())
        );

        $this->addDefaultFooter(true);
    }

    public function validated(Request $request): array
    {
        $params = $request->validate([
            'core.license.id'  => 'required|string',
            'core.license.key' => 'required|string',
        ]);

        $licenseId  = Arr::get($params, 'core.license.id');
        $licenseKey = Arr::get($params, 'core.license.key');

        // pass if the same value with current values.

        try {
            app(MetaFoxStore::class)->verifyLicense($licenseId, $licenseKey);

            // update license status as activated
            Arr::set($params, 'core.license.valid', 1);

            // fix: MFOXCORE-7293
            PackageManager::updateEnvironmentFile([
                'MFOX_LICENSE_ID'  => $licenseId,
                'MFOX_LICENSE_KEY' => $licenseKey,
            ]);
        } catch (\Throwable $error) {
            Log::channel('daily')->error($error->getMessage());
            abort(422, __p('core::validation.license_credentials_were_incorrect'));
        }

        return $params;
    }
}
