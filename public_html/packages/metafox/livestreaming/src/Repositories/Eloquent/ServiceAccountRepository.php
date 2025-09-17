<?php

namespace MetaFox\LiveStreaming\Repositories\Eloquent;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MetaFox\Core\Models\SiteSetting;
use MetaFox\Core\Repositories\Eloquent\SiteSettingRepository;
use MetaFox\LiveStreaming\Repositories\ServiceAccountRepositoryInterface;
use MetaFox\Platform\Contracts\SiteSettingRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class UserStreamKeyRepository.
 */
class ServiceAccountRepository extends AbstractRepository implements ServiceAccountRepositoryInterface
{
    public const SETTING_NAME = 'livestreaming.firebase_service_account_file';

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
        $path = implode(DIRECTORY_SEPARATOR, ['livestreaming', $file->getClientOriginalName()]);
        $disk->putFileAs('livestreaming', $file, $file->getClientOriginalName());

        /** @var SiteSettingRepository $siteSettingRepo */
        $siteSettingRepo = resolve(SiteSettingRepositoryInterface::class);
        $siteSettingRepo->updateSetting('livestreaming', self::SETTING_NAME, null, null, $path, 'string', false, true);
        localCacheStore()->clear();

        return true;
    }
}
