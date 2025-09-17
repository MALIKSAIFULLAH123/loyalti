<?php

namespace MetaFox\Event\Database\Importers;

use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Event\Models\Member as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class MemberImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'event_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user', '$event',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
        $this->transformPrivacyMember([MetaFoxPrivacy::FRIENDS], '$event', '$user');
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'],
            'user_id'    => $entry['user_id'] ?? null,
            'user_type'  => $entry['user_type'] ?? null,
            'event_id'   => $entry['event_id'],
            'rsvp_id'    => $this->handleRsvp($entry['rsvp_id'] ?? null),
            'role_id'    => $this->handleRole($entry['role_id'] ?? null),
            'updated_at' => $entry['updated_at'] ?? null,
            'created_at' => $entry['created_at'] ?? null,
        ]);
    }

    private function handleRole(?int $roleId): int
    {
        $roleList = [Model::ROLE_MEMBER, Model::ROLE_HOST];

        if ($roleId && in_array($roleId, $roleList)) {
            return $roleId;
        }

        return Model::ROLE_MEMBER;
    }

    private function handleRsvp(?int $rsvpId): int
    {
        $rsvpId = match ((int) $rsvpId) {
            3       => Model::NOT_INTERESTED,
            default => $rsvpId,
        };

        $rsvpList = [Model::NOT_INTERESTED, Model::JOINED, Model::INTERESTED, Model::INVITED];

        if ($rsvpId && in_array($rsvpId, $rsvpList)) {
            return $rsvpId;
        }

        return Model::NOT_INTERESTED;
    }
}
