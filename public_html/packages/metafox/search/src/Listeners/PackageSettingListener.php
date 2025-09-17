<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Search\Listeners;

use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Search\Models\Type;
use MetaFox\Search\Policies\TypePolicy;

/**
 * Class PackageSettingListener.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getEvents(): array
    {
        return [
            'models.notify.created' => [
                ModelCreatedListener::class,
            ],
            'models.notify.updated' => [
                ModelUpdatedListener::class,
            ],
            'models.notify.deleted' => [
                ModelDeletedListener::class,
            ],
            'search.created' => [
                ModelCreatedListener::class,
            ],
            'search.updated' => [
                ModelUpdatedListener::class,
            ],
            'search.deleted' => [
                ModelDeletedListener::class,
            ],
            'packages.deleting' => [
                PackageDeletingListener::class,
            ],
            'search.update_search_text' => [
                UpdateSearchTextListener::class,
            ],
            'models.notify.approved' => [
                ModelApprovedListener::class,
            ],
            'search.delete_item' => [
                DeleteSearchItemListener::class,
            ],
            'search.update_item' => [
                UpdateSearchItemListener::class,
            ],
        ];
    }

    public function getPolicies(): array
    {
        return [
            Type::class => TypePolicy::class,
        ];
    }
}
