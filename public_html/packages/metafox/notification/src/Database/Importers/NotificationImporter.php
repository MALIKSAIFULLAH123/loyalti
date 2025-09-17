<?php

namespace MetaFox\Notification\Database\Importers;

use MetaFox\Notification\Repositories\TypeRepositoryInterface;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Notification\Models\Notification as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class NotificationImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'notifiable_id', 'item_id'];
    private array $notificationTypes;

    public function __construct()
    {
        $this->notificationTypes = resolve(TypeRepositoryInterface::class)
            ->getAllNotificationType();
    }

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$notifiable', '$user', '$item',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $oid = $entry['$oid'];

        if (!$this->checkNotificationType($entry['type'])) {
            return;
        }

        $this->addEntryToBatch(
            Model::class,
            [
                'id'              => $oid,
                'type'            => $entry['type'] ?? null,
                'notifiable_id'   => $entry['notifiable_id'] ?? null,
                'notifiable_type' => $entry['notifiable_type'] ?? null,
                'item_id'         => $entry['item_id'] ?? null,
                'item_type'       => $entry['item_type'] ?? null,
                'user_id'         => $entry['user_id'] ?? null,
                'user_type'       => $entry['user_type'] ?? null,
                'data'            => $entry['data'] ?? null,
                'read_at'         => $entry['read_at'] ?? null,
                'notified_at'     => $entry['notified_at'] ?? null,
                'is_request'      => $entry['is_request'] ?? 0,
                'created_at'      => $entry['created_at'] ?? null,
                'updated_at'      => $entry['updated_at'] ?? null,
                'deleted_at'      => $entry['deleted_at'] ?? null,
            ]
        );
    }

    private function checkNotificationType(?string $type): bool
    {
        if ($type && in_array($type, $this->notificationTypes)) {
            return true;
        }

        return false;
    }
}
