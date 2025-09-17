<?php

namespace MetaFox\Event\Database\Importers;

use MetaFox\Event\Models\EventTagData;
use MetaFox\Event\Models\EventText;
use MetaFox\Event\Models\PrivacyStream;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Event\Models\Event as Model;
use MetaFox\User\Models\UserEntity;
use MetaFox\User\Support\Facades\User as UserFacade;

/*
 * stub: packages/database/json-importer.stub
 */

class EventImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function afterPrepare(): void
    {
        $this->appendFileBundle('$image');
        $this->transformPrivacyList(MetaFoxPrivacy::ONLY_ME, Model::EVENT_OWNER, '$user', '$id');
        $this->transformPrivacyList(MetaFoxPrivacy::CUSTOM, Model::EVENT_HOSTS, '$user', '$id');
        $this->transformPrivacyList(MetaFoxPrivacy::FRIENDS, Model::EVENT_MEMBERS, '$user', '$id');
        $this->processPrivacyStream(PrivacyStream::class);
        $this->transformPrivacyMember([MetaFoxPrivacy::ONLY_ME, MetaFoxPrivacy::CUSTOM, MetaFoxPrivacy::FRIENDS], '$id');
        $this->transformPrivacyMember([MetaFoxPrivacy::ONLY_ME, MetaFoxPrivacy::CUSTOM], '$id', '$owner');
        $this->transformActivitySubscription('$id', '$id');
    }

    public function processImport()
    {
        $this->remapRefs([
            '$owner', '$user',
            '$image.$id' => ['image_file_id'],
        ]);
        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
        $this->upsertBatchEntriesInChunked(EventText::class, ['id']);
        $this->upsertBatchEntriesInChunked(UserEntity::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $oid  = $entry['$oid'];
        $name = html_entity_decode($entry['name'] ?? '');

        $this->addEntryToBatch(
            Model::class,
            [
                'id'                   => $oid,
                'name'                 => $name,
                'module_id'            => $entry['module_id'] ?? Model::ENTITY_TYPE,
                'privacy'              => $this->privacyMapEntry($entry),
                'is_featured'          => $entry['is_featured'] ?? 0,
                'is_sponsor'           => $entry['is_sponsor'] ?? 0,
                'is_approved'          => $entry['is_approved'] ?? 1,
                'pending_mode'         => $entry['pending_mode'] ?? 0,
                'start_time'           => $entry['start_time'] ?? null,
                'end_time'             => $entry['end_time'] ?? null,
                'is_online'            => $entry['is_online'] ?? 0,
                'event_url'            => $entry['event_url'] ?? null,
                'location_latitude'    => $entry['location_latitude'] ?? null,
                'location_longitude'   => $entry['location_longitude'] ?? null,
                'location_name'        => isset($entry['location_name']) ? html_entity_decode($entry['location_name']) : null,
                'country_iso'          => $entry['country_iso'] ?? null,
                'tags'                 => json_encode($entry['tags'] ?? []),
                'user_id'              => $entry['user_id'] ?? null,
                'user_type'            => $entry['user_type'] ?? null,
                'owner_id'             => $entry['owner_id'] ?? $entry['user_id'],
                'owner_type'           => $entry['owner_type'] ?? $entry['user_type'],
                'image_file_id'        => $entry['image_file_id'] ?? null,
                'updated_at'           => $entry['updated_at'] ?? null,
                'created_at'           => $entry['created_at'] ?? null,
                'total_like'           => $entry['total_like'] ?? 0,
                'total_share'          => $entry['total_share'] ?? 0,
                'total_feed'           => $entry['total_feed'] ?? 0,
                'total_member'         => $entry['total_member'] ?? 0,
                'total_attachment'     => $entry['total_attachment'] ?? 0,
                'total_view'           => $entry['total_view'] ?? 0,
                'total_interested'     => $entry['total_interested'] ?? 0,
                'total_pending_invite' => $entry['total_pending_invite'] ?? 0,
                'view_id'              => $this->handleViewType($entry['view_id'] ?? null),
                'featured_at'          => $entry['featured_at'] ?? null,
                'sponsor_in_feed'      => $entry['sponsor_in_feed'] ?? 0,
            ]
        );

        $this->addEntryToBatch(
            EventText::class,
            [
                'id'          => $oid,
                'text'        => $entry['text'] ?? '',
                'text_parsed' => $this->parseText($entry['text_parsed'] ?? '', false),
            ]
        );

        $this->addEntryToBatch(UserEntity::class, [
            'id'             => $oid,
            'entity_type'    => Model::ENTITY_TYPE,
            'user_name'      => $entry['user_name'] ?? null,
            'name'           => $name,
            'short_name'     => UserFacade::getShortName($name),
            'avatar_id'      => $entry['image_file_id'] ?? null,
            'avatar_type'    => isset($entry['image_file_id']) ? 'photo' : null,
            'avatar_file_id' => $entry['image_file_id'] ?? null,
            'is_featured'    => $entry['is_featured'] ?? 0,
            'is_searchable'  => $entry['is_searchable'] ?? 1,
            'is_suggestion_searchable'  => false,
            'gender'         => $entry['gender'] ?? 0,
            'deleted_at'     => $entry['deleted_at'] ?? null,
        ]);
    }

    private function handleViewType(?int $viewId): int
    {
        $viewList = [2, 0];

        if ($viewId && in_array($viewId, $viewList)) {
            return $viewId;
        }

        return 0;
    }

    public function afterImport(): void
    {
        $this->importTagData(EventTagData::class);
    }
}
