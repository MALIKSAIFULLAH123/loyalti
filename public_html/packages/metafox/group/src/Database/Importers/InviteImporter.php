<?php

namespace MetaFox\Group\Database\Importers;

use MetaFox\Group\Models\Invite as Model;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Group\Support\InviteType;

/*
 * stub: packages/database/json-importer.stub
 */

class InviteImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'group_id', 'owner_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$user', '$group', '$owner']);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'          => $entry['$oid'] ?? null,
            'user_id'     => $entry['user_id'] ?? null,
            'owner_id'    => $entry['owner_id'] ?? null,
            'user_type'   => $entry['user_type'] ?? null,
            'owner_type'  => $entry['owner_type'] ?? null,
            'group_id'    => $entry['group_id'] ?? null,
            'expired_at'  => $entry['expired_at'] ?? null,
            'invite_type' => $this->handleInviteType($entry['invite_type'] ?? null),
            'code'        => $entry['code'] ?? null,
            'status_id'   => $this->handleStatus($entry['status_id'] ?? null),
            'created_at'  => $entry['created_at'] ?? null,
            'updated_at'  => $entry['updated_at'] ?? null,
        ]);
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
        $inviteTypeList = [
            InviteType::INVITED_MEMBER,
            InviteType::INVITED_ADMIN_GROUP,
            InviteType::INVITED_MODERATOR_GROUP,
            InviteType::INVITED_GENERATE_LINK,
        ];

        if ($inviteType && in_array($inviteType, $inviteTypeList)) {
            return $inviteType;
        }

        return InviteType::INVITED_MEMBER;
    }
}
