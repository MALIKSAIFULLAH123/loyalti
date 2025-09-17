<?php

namespace MetaFox\Core\Support;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\App\Support\MetaFoxStore;
use MetaFox\Platform\Facades\Settings;

class License
{
    /**
     * fetch license details.
     * @return array
     */
    public function detail(): array
    {
        $licenseId  = Settings::get('core.license.id', '');
        $licenseKey = Settings::get('core.license.key', '');

        $detail = app(MetaFoxStore::class)->getLicenseDetails($licenseId, $licenseKey);

        return $detail ?? [];
    }

    /**
     * refresh license status.
     * @return array<mixed>
     */
    public function refresh(): array
    {
        $latestLicense               = $this->detail();
        $currentLicense              = Settings::get('core.license') ?? [];
        $currentLicenseStatus        = Arr::get($currentLicense, 'valid');
        $latestLicenseStatus         = (int) Arr::get($latestLicense, 'valid', 0);
        $latestLicenseExpiredDay     = (int) Arr::get($latestLicense, 'license.renewal_expired_date') ?? Carbon::now()->timestamp;

        Arr::set($currentLicense, 'expired_at', Carbon::createFromTimestamp($latestLicenseExpiredDay));

        if ($currentLicenseStatus !== $latestLicenseStatus) {
            $result = [
                'valid'        => $latestLicenseStatus,
                'pricing_type' => Arr::get($latestLicense, 'license.pricing_type', ''),
                'error'        => Arr::get($latestLicense, 'error', __p('health-check::web.license_inactive')),
            ];

            $currentLicense = array_merge($currentLicense, $result);
        }

        Settings::save([
            'core.license' => $currentLicense,
        ]);

        Artisan::call('cache:reset');

        return $currentLicense;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        $licenseStatus = Settings::get('core.license.valid');
        if (!isset($licenseStatus)) {
            // force fetching license for the first time
            $licenseStatus = Arr::get($this->refresh(), 'valid', 0);
        }

        return (bool) $licenseStatus;
    }

    public function deactivate(): void
    {
        if (!$this->isActive()) {
            return;
        }

        $currentLicense = Settings::get('core.license');

        Settings::save([
            'core.license' => array_merge($currentLicense, ['valid' => 0]),
        ]);

        Artisan::call('cache:reset');
    }
}
