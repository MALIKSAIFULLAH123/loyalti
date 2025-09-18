<?php

namespace MetaFox\InAppPurchase\Repositories\Eloquent;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MetaFox\Core\Models\SiteSetting;
use MetaFox\Core\Repositories\Eloquent\SiteSettingRepository;
use MetaFox\InAppPurchase\Repositories\GoogleServiceAccountRepositoryInterface;
use MetaFox\Platform\Contracts\SiteSettingRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class UserStreamKeyRepository.
 */
class GoogleServiceAccountRepository extends AbstractRepository implements GoogleServiceAccountRepositoryInterface
{
    public const SETTING_NAME = 'in-app-purchase.google_service_account_file';

    public function model()
    {
        return SiteSetting::class;
    }

    /**
     * @param  array<string, mixed> $attributes
     * @return bool
     */
    public function create(array $attributes): bool
    {
        // Get files from request.
        /** @var UploadedFile $file */
        $file = $attributes['file'];
        $disk = Storage::disk('local');
        $path = implode(DIRECTORY_SEPARATOR, ['in-app-purchase', $file->getClientOriginalName()]);
        $disk->putFileAs('in-app-purchase', $file, $file->getClientOriginalName());

        /** @var SiteSettingRepository $siteSettingRepo */
        $siteSettingRepo = resolve(SiteSettingRepositoryInterface::class);
        $siteSettingRepo->updateSetting('in-app-purchase', self::SETTING_NAME, null, null, $path, 'string', false, true);
        localCacheStore()->clear();

        return true;
    }
}
