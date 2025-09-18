<?php

namespace MetaFox\Poll\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Poll\Models\Design;
use MetaFox\Poll\Models\Poll as Model;
use MetaFox\Poll\Models\PollText;
use MetaFox\Poll\Models\PrivacyStream;
use MetaFox\Poll\Support\Facade\Poll as PollFacade;

/*
 * stub: packages/database/json-importer.stub
 */

class PollImporter extends JsonImporter
{
    protected bool $priorityKeepOldIdSetting = true;

    protected array $requiredColumns = ['user_id'];

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
        $this->remapRefs([
            '$owner', '$user',
            '$image.$id' => ['image_file_id'],
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
        $this->upsertBatchEntriesInChunked(PollText::class, ['id']);
        $this->upsertBatchEntriesInChunked(Design::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $oid = $entry['$oid'];

        $this->addEntryToBatch(
            Model::class,
            [
                'id'                 => $oid,
                'view_id'            => $this->handleViewType($entry['view_id'] ?? null),
                'privacy'            => $this->privacyMapEntry($entry),
                'question'           => html_entity_decode($entry['question'] ?? ''),
                'caption'            => $entry['caption'] ?? null,
                'randomize'          => $entry['randomize'] ?? 1,
                'public_vote'        => $entry['public_vote'] ?? 0,
                'is_multiple'        => $entry['is_multiple'] ?? 0,
                'closed_at'          => $entry['closed_at'] ?? null,
                'is_approved'        => $entry['is_approved'] ?? 1,
                'is_featured'        => $entry['is_featured'] ?? 0,
                'is_sponsor'         => $entry['is_sponsor'] ?? 0,
                'sponsor_in_feed'    => $entry['sponsor_in_feed'] ?? 0,
                'total_like'         => $entry['total_like'] ?? 0,
                'total_comment'      => $entry['total_comment'] ?? 0,
                'total_reply'        => $entry['total_reply'] ?? 0,
                'total_share'        => $entry['total_share'] ?? 0,
                'total_view'         => $entry['total_view'] ?? 0,
                'total_attachment'   => $entry['total_attachment'] ?? 0,
                'total_vote'         => $entry['total_vote'] ?? 0,
                'updated_at'         => $entry['updated_at'] ?? null,
                'image_file_id'      => $entry['image_file_id'] ?? null,
                'created_at'         => $entry['created_at'] ?? null,
                'location_latitude'  => $entry['location_latitude'] ?? null,
                'location_longitude' => $entry['location_longitude'] ?? null,
                'location_name'      => isset($entry['location_name']) ? html_entity_decode($entry['location_name']) : null,
                'featured_at'        => $entry['featured_at'] ?? null,
                'user_id'            => $entry['user_id'] ?? null,
                'user_type'          => $entry['user_type'] ?? null,
                'owner_id'           => $entry['owner_id'] ?? $entry['user_id'],
                'owner_type'         => $entry['owner_type'] ?? $entry['user_type'],
            ]
        );

        $this->addEntryToBatch(
            PollText::class,
            [
                'id'          => $oid,
                'text'        => $entry['text'] ?? '',
                'text_parsed' => $this->parseText($entry['text_parsed'] ?? ''),
            ]
        );

        $this->addEntryToBatch(
            Design::class,
            [
                'id' => $oid,
            ]
        );
    }

    private function handleViewType(?int $viewId): int
    {
        $viewList = [PollFacade::getIntegrationViewId(), 0];

        if ($viewId && in_array($viewId, $viewList)) {
            return $viewId;
        }

        return 0;
    }
}
