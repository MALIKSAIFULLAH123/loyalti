<?php

namespace MetaFox\Marketplace\Database\Importers;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Arr;
use MetaFox\Marketplace\Models\Listing as Model;
use MetaFox\Marketplace\Models\ListingTagData;
use MetaFox\Marketplace\Models\PrivacyStream;
use MetaFox\Marketplace\Models\Text;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class ListingImporter extends JsonImporter
{
    protected bool $priorityKeepOldIdSetting = true;

    protected array $requiredColumns = [
        'user_id',
        'owner_id',
        'user_type',
        'owner_type',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function afterPrepare(): void
    {
        $this->appendFileBundle('$image');
        $this->processPrivacyStream(PrivacyStream::class);
    }

    public function processImport()
    {
        $this->remapRefs(['$user', '$owner', '$image.$id' => ['image_file_id']]);
        $this->remapCurrency();
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
        $this->upsertBatchEntriesInChunked(Text::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $timeExpired = $this->getTimeStamp($entry);

        $this->addEntryToBatch(Model::class, [
            'id'                  => $entry['$oid'],
            'title'               => html_entity_decode($entry['title'] ?? ''),
            'privacy'             => $this->privacyMapEntry($entry),
            'user_id'             => $entry['user_id'] ?? null,
            'user_type'           => $entry['user_type'] ?? null,
            'owner_id'            => $entry['owner_id'] ?? $entry['user_id'],
            'owner_type'          => $entry['owner_type'] ?? $entry['user_type'],
            'is_featured'         => $entry['is_featured'] ?? 0,
            'is_sponsor'          => $entry['is_sponsor'] ?? 0,
            'is_approved'         => $entry['is_approved'] ?? 1,
            'featured_at'         => $entry['featured_at'] ?? null,
            'sponsor_in_feed'     => $entry['sponsor_in_feed'] ?? 0,
            'tags'                => isset($entry['tags']) ? json_encode($entry['tags']) : null,
            'total_like'          => $entry['total_like'] ?? 0,
            'total_share'         => $entry['total_share'] ?? 0,
            'total_attachment'    => $entry['total_attachment'] ?? 0,
            'total_view'          => $entry['total_view'] ?? 0,
            'allow_payment'       => $entry['allow_payment'] ?? false,
            'allow_point_payment' => $entry['allow_point_payment'] ?? false,
            'auto_sold'           => $entry['auto_sold'] ?? false,
            'is_sold'             => $entry['is_sold'] ?? false,
            'is_notified'         => $entry['is_notified'] ?? false,
            'price'               => $this->handlePrice($entry),
            'short_description'   => $this->parseText($entry['short_description'] ?? '', false),
            'image_file_id'       => $entry['image_file_id'] ?? null,
            'location_latitude'   => $entry['location_latitude'] ?? null,
            'location_longitude'  => $entry['location_longitude'] ?? null,
            'location_name'       => isset($entry['location_name']) ? html_entity_decode($entry['location_name']) : null,
            'country_iso'         => $entry['country_iso'] ?? null,
            'updated_at'          => $entry['updated_at'] ?? null,
            'deleted_at'          => $entry['deleted_at'] ?? null,
            'created_at'          => $entry['created_at'] ?? null,
            'start_expired_at'    => Arr::get($timeExpired, 'expired_at'),
            'notify_at'           => Arr::get($timeExpired, 'notify_at'),
        ]);

        $this->addEntryToBatch(Text::class, [
            'id'          => $entry['$oid'],
            'text'        => $entry['text'] ?? '',
            'text_parsed' => $this->parseText($entry['text_parsed'] ?? ''),
        ]);
    }

    private function handlePrice(array $entry): string
    {
        if (!is_numeric($entry['price'])) {
            return '';
        }

        $currency = $entry['currency_id'] ?? null;
        if (!$currency) {
            return '';
        }

        return json_encode([
            $currency => $entry['price'],
        ]);
    }

    public function afterImport(): void
    {
        $this->importTagData(ListingTagData::class);
    }

    private function getTimeStamp(array $entry): array
    {
        $createdAt = Arr::get($entry, 'created_at') ?? Carbon::now()->toIso8601String();

        $days       = (int) Settings::get('marketplace.days_to_expire', 30);
        $notifyDays = (int) Settings::get('marketplace.days_to_notify_before_expire', 0);

        if ($days == 0) {
            return [
                'expired_at' => 0,
                'notify_at'  => 0,
            ];
        }

        $timestamp       = Carbon::createFromFormat(DateTimeInterface::ISO8601, $createdAt)->addDays($days)->timestamp;
        $notifyTimestamp = $notifyDays > 0 ? Carbon::parse($timestamp)->subDays($notifyDays)->timestamp : 0;

        return [
            'expired_at' => $timestamp,
            'notify_at'  => $notifyTimestamp,
        ];
    }
}
