<?php

namespace MetaFox\Group\Database\Importers;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Member as Model;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\UserShortcut;

/*
 * stub: packages/database/json-importer.stub
 */

class MemberImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'group_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function afterPrepare(): void
    {
        $this->transformPrivacyMember([], '$group', '$user');
        $this->transformActivitySubscription('$user', '$group');
        $this->appendShortCutsBundle();
    }

    public function processImport()
    {
        $this->remapRefs(['$group', '$user']);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'          => $entry['$oid'] ?? null,
            'user_id'     => $entry['user_id'] ?? null,
            'user_type'   => $entry['user_type'] ?? null,
            'group_id'    => $entry['group_id'] ?? null,
            'member_type' => $this->handleMemberType($entry['member_type'] ?? null),
            'created_at'  => $entry['created_at'] ?? null,
            'updated_at'  => $entry['updated_at'] ?? null,
        ]);
    }

    private function handleMemberType(?int $type): int
    {
        $memberTypes = [Model::MEMBER, Model::ADMIN, Model::MODERATOR];

        if ($type && in_array($type, $memberTypes)) {
            return $type;
        }

        return Model::MEMBER;
    }

    public function getPrivacyList($entry): array
    {
        $privacyList = [Group::MEMBER_PRIVACY];

        if ($entry['member_type'] == 1) {
            $privacyList[] = Group::ADMIN_PRIVACY;
        }

        if ($entry['member_type'] == 2) {
            $privacyList[] = Group::ADMIN_PRIVACY . '.gm';
        }

        return $privacyList;
    }

    public function appendShortCutsBundle(): void
    {
        $callback = function (array &$output, $entry) {
            $output[] = [
                '$id'   => 'sc.' . $entry['$id'],
                '$user' => $entry['$user'] ?? null,
                '$item' => $entry['$group'] ?? null,
            ];
        };

        $this->exportBundled(UserShortcut::ENTITY_TYPE, 10, '$id', $callback);
    }
}
