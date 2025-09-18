<?php

namespace MetaFox\Subscription\Database\Importers;

use Illuminate\Support\Arr;
use MetaFox\Importer\Models\Entry;
use MetaFox\Importer\Repositories\EntryRepositoryInterface;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Storage\Models\StorageFile;
use MetaFox\Subscription\Models\SubscriptionPackage as Model;
use MetaFox\Subscription\Models\SubscriptionPackageText;
use MetaFox\Subscription\Support\Helper;

/*
 * stub: packages/database/json-importer.stub
 */

class SubscriptionPackageImporter extends JsonImporter
{
    protected array $requiredColumns = ['upgradedRole_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function afterPrepare(): void
    {
        $this->appendFileBundle('$image');
    }

    public function processImport()
    {
        $this->remapRefs(['$upgradedRole', '$image.$id' => ['image_file_id']]);

        $this->remapImage();

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
        $this->upsertBatchEntriesInChunked(SubscriptionPackageText::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $isFree = $entry['is_free'] ?? false;

        $this->addEntryToBatch(Model::class, [
            'id'                                            => $entry['$oid'] ?? null,
            'title'                                         => html_entity_decode($entry['title'] ?? ''),
            'is_on_registration'                            => $entry['is_on_registration'] ?? false,
            'is_popular'                                    => $entry['is_popular'] ?? false,
            'is_free'                                       => $isFree,
            'total_success'                                 => $entry['total_success'] ?? 0,
            'total_pending'                                 => $entry['total_pending'] ?? 0,
            'total_canceled'                                => $entry['total_canceled'] ?? 0,
            'total_expired'                                 => $entry['total_expired'] ?? 0,
            'ordering'                                      => $entry['ordering'] ?? 0,
            'days_notification_before_subscription_expired' => $entry['days_notification_before_subscription_expired'] ?? 0,
            'background_color_for_comparison'               => $entry['background_color_for_comparison'] ?? null,
            'upgraded_role_id'                              => $entry['upgradedRole_id'] ?? null,
            'image_file_id'                                 => $entry['image_file_id'] ?? null,
            'image_path'                                    => $entry['image_path'] ?? null,
            'image_server_id'                               => $entry['image_server_id'] ?? 'public',
            'recurring_period'                              => $isFree ? null : $this->handleRecurringPeriod($entry['recurring_period'] ?? null),
            'status'                                        => $this->handleStatus($entry['status'] ?? null),
            'allowed_renew_type'                            => $this->handleAllowedRenewType($entry),
            'visible_roles'                                 => $this->handleVisibleRoles($entry),
            'price'                                         => $this->handlePrice($entry, '$price'),
            'recurring_price'                               => $isFree ? null : $this->handlePrice($entry, '$recurring_price'),
            'created_at'                                    => $entry['created_at'] ?? null,
            'updated_at'                                    => $entry['updated_at'] ?? null,
        ]);

        $this->addEntryToBatch(SubscriptionPackageText::class, [
            'id'          => $entry['$oid'],
            'text'        => $entry['text'] ?? '',
            'text_parsed' => $entry['text_parsed'] ?? '',
        ]);
    }

    private function remapImage(): void
    {
        $values = $this->pickEntriesValue('image_file_id');

        $map  = [];
        $rows = StorageFile::query()->whereIn('id', $values)
            ->get(['id', 'path', 'storage_id'])
            ->toArray();

        array_map(function ($row) use (&$map) {
            $map[$row['id']] = [$row['path'], $row['storage_id']];
        }, $rows);

        foreach ($this->entries as &$entry) {
            $key = Arr::get($entry, 'image_file_id');

            if (!$key) {
                continue;
            }

            $item = $map[$key] ?? null;

            if (!$item) {
                continue;
            }

            $entry['image_path']      = $item[0];
            $entry['image_server_id'] = $item[1];
        }
    }

    private function handleVisibleRoles(array $entry): ?string
    {
        $roles = json_decode(Arr::get($entry, '$visible_roles'));

        if (empty($roles)) {
            return null;
        }

        $result = [];

        foreach ($roles as $role) {
            $roleEntry = resolve(EntryRepositoryInterface::class)
                ->getEntry($role, 'phpfox');

            if ($roleEntry instanceof Entry) {
                $result[] = $roleEntry->resource_id;
            }
        }

        return json_encode($result);
    }

    private function handleAllowedRenewType(array $entry): ?string
    {
        $types = json_decode(Arr::get($entry, 'allowed_renew_type'));

        if (empty($types)) {
            return null;
        }

        foreach ($types as $type) {
            if (!in_array($type, Helper::getRenewType())) {
                unset($types[$type]);
            }
        }

        return json_encode($types);
    }

    private function handleRecurringPeriod(?string $period): ?string
    {
        if ($period == null || in_array($period, Helper::getRecurringPeriodType())) {
            return $period;
        }

        return Helper::RECURRING_PERIOD_ANNUALLY;
    }

    private function handlePrice(array $entry, string $name): ?string
    {
        $prices = json_decode(Arr::get($entry, $name), true) ?? [];
        $result = [];

        foreach ($prices as $key => $price) {
            $currencyEntry = resolve(EntryRepositoryInterface::class)
                ->getEntry($key, 'phpfox');

            if (!$currencyEntry instanceof Entry) {
                continue;
            }

            $currency          = explode('#', $currencyEntry->ref_id)[1];
            $result[$currency] = (int) $price;
        }

        if (empty($result)) {
            return null;
        }

        return json_encode($result);
    }

    private function handleStatus(?string $status): string
    {
        $statusList = [Helper::STATUS_ACTIVE, Helper::STATUS_DEACTIVE, Helper::STATUS_DELETED];

        if ($status && in_array($status, $statusList)) {
            return $status;
        }

        return Helper::STATUS_ACTIVE;
    }
}
