<?php

namespace MetaFox\Page\Database\Importers;

use Illuminate\Support\Carbon;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Page\Models\PageInvite as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class PageInviteImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'page_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user',
            '$owner',
            '$page',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $oid = $entry['$oid'];

        $this->addEntryToBatch(
            Model::class,
            [
                'id'          => $oid,
                'page_id'     => $entry['page_id'] ?? 0,
                'status_id'   => $this->handleStatus($entry['status_id'] ?? null),
                'user_id'     => $entry['user_id'] ?? null,
                'user_type'   => $entry['user_type'] ?? null,
                'owner_id'    => $entry['owner_id'] ?? $entry['user_id'],
                'owner_type'  => $entry['owner_type'] ?? $entry['user_type'],
                'updated_at'  => $entry['updated_at'] ?? null,
                'created_at'  => $entry['created_at'] ?? null,
                'expired_at'  => Carbon::now()->addDays(Model::EXPIRE_DAY),
                'invite_type' => $this->handleInviteType($entry['invite_type'] ?? null),
            ]
        );
    }

    private function handleStatus(?int $statusId): int
    {
        $statusList = [
            Model::STATUS_PENDING, Model::STATUS_APPROVED,
            Model::STATUS_NOT_INVITE_AGAIN, Model::STATUS_NOT_USE,
        ];

        if ($statusId && in_array($statusId, $statusList)) {
            return $statusId;
        }

        return Model::STATUS_PENDING;
    }

    private function handleInviteType(?string $inviteType): string
    {
        $inviteTypeList = [Model::INVITE_MEMBER, Model::INVITE_ADMIN];

        if ($inviteType && in_array($inviteType, $inviteTypeList)) {
            return $inviteType;
        }

        return Model::INVITE_MEMBER;
    }
}
