<?php

namespace MetaFox\App\Http\Resources\v1\Package\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use MetaFox\App\Models\Package as Model;

/**
 * Class PackageItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PackageItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $obj       = $this->resource;
        $expiredAt = $obj->expired_at;

        [$updateAvailableText, $updateAvailableLink, $updateAvailableHint] = $this->getUpdateAvailable($obj);

        return [
            'id'                     => $obj->id,
            'name'                   => $obj->name,
            'title'                  => $obj->title,
            'version'                => $obj->version,
            'latest_version'         => $obj->latest_version ?? $obj->version,
            'is_active'              => $obj->is_active,
            'is_installed'           => $obj->is_installed,
            'is_purchased'           => $obj->is_purchased,
            'store_id'               => $obj->store_id,
            'is_force_upgrade'       => $this->shouldForceUpgrade($obj),
            'purchased_at'           => $obj->purchased_at,
            'is_core'                => $obj->is_core,
            'author'                 => [
                'name' => $obj->author,
                'url'  => $obj->author_url,
            ],
            'internal_url'           => $obj->internal_url,
            'expired_at'             => $expiredAt,
            'is_expired'             => $expiredAt && Carbon::parse($expiredAt)->lt(Carbon::now()),
            'internal_admin_url'     => $obj->is_active ? $obj->internal_admin_url : '',
            'type'                   => Str::headline($obj->type),
            'upgrade_available'      => $updateAvailableText,
            'upgrade_available_link' => $updateAvailableLink,
            'upgrade_available_hint' => $updateAvailableHint,
        ];
    }

    /**
     * @param Model $package
     * @return array<int, mixed>
     */
    protected function getUpdateAvailable(Model $package): array
    {
        $detailLink = $textHint = '';
        $textPhrase = __p('core::phrase.up_to_date');

        if ($package->store_id) {
            $detailLink = url_utility()->makeApiFullUrl('/admincp/app/store/product/' . $package->store_id);
            $textHint   = __p('app::phrase.view_details');
        }

        if (!$package->is_purchased) {
            return [$textPhrase, $detailLink, $textHint];
        }

        if (version_compare($package->latest_version, $package->version, 'gt')) {
            $textPhrase = __p('app::phrase.update_now');
            $textHint   = __p('app::phrase.view_version_details', ['version' => $package->latest_version]);
        }

        return [$textPhrase, $detailLink, $textHint];
    }

    protected function shouldForceUpgrade(Model $package): bool
    {
        if (!$package->store_id || !$package->is_purchased) {
            return false;
        }

        return !empty($package->latest_version) && version_compare($package->latest_version, $package->version, 'gt');
    }
}
