<?php

namespace MetaFox\Page\Database\Importers;

use MetaFox\Page\Models\Page;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Page\Models\PageMember as Model;
use MetaFox\User\Models\UserShortcut;

/*
 * stub: packages/database/json-importer.stub
 */

class PageMemberImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'page_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function afterPrepare(): void
    {
        $this->transformPrivacyMember([], '$page', '$user');
        $this->transformActivitySubscription('$user', '$page');
        $this->appendShortCutsBundle();
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user',
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
                'user_id'     => $entry['user_id'] ?? null,
                'user_type'   => $entry['user_type'] ?? null,
                'updated_at'  => $entry['updated_at'] ?? null,
                'created_at'  => $entry['created_at'] ?? null,
                'member_type' => $this->handleMemberType($entry['member_type'] ?? null),
            ]
        );
    }

    private function handleMemberType(?int $type): int
    {
        $memberTypes = [Model::MEMBER, Model::ADMIN];

        if ($type && in_array($type, $memberTypes)) {
            return $type;
        }

        return Model::MEMBER;
    }

    public function getPrivacyList($entry): array
    {
        $privacyList = [Page::MEMBER_PRIVACY];

        if ($entry['member_type'] == 1) {
            $privacyList[] = Page::ADMIN_PRIVACY;
        }

        return $privacyList;
    }

    public function appendShortCutsBundle(): void
    {
        $callback = function (array &$output, $entry) {
            $output[] = [
                '$id'   => 'sc.' . $entry['$id'],
                '$user' => $entry['$user'] ?? null,
                '$item' => $entry['$page'] ?? null,
            ];
        };

        $this->exportBundled(UserShortcut::ENTITY_TYPE, 10, '$id', $callback);
    }
}
