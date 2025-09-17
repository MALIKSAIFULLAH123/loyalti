<?php

namespace MetaFox\Group\Database\Importers;

use MetaFox\Group\Models\GroupText;
use MetaFox\Group\Models\PrivacyStream;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Group\Models\Group as Model;
use MetaFox\User\Models\UserEntity;
use MetaFox\User\Support\Facades\User as UserFacade;

/*
 * stub: packages/database/json-importer.stub
 */

class GroupImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'category_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function afterPrepare(): void
    {
        $this->appendPhotoBundle('$avatar', 3, 'avatar_id', Model::class);
        $this->appendPhotoBundle('$cover', 3, 'cover_id', Model::class);
        $this->transformPrivacyList(Model::MEMBER_PRIVACY, 'group_members', '$user', '$id');
        $this->transformPrivacyList(Model::ADMIN_PRIVACY, 'group_admins', '$user', '$id');
        $this->transformPrivacyList(Model::ADMIN_PRIVACY, 'group_moderators', '$user', '$id', '.gm');
        $this->processPrivacyStream(PrivacyStream::class, 'privacy', 'privacy_list', '$user');
        $this->transformPrivacyMember([Model::MEMBER_PRIVACY, Model::ADMIN_PRIVACY, Model::ADMIN_PRIVACY . '.gm'], '$id');
        $this->transformPrivacyMember([Model::ADMIN_PRIVACY . '.gm'], '$id', '$user');
        $this->transformUserPrivacy();
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user',
            '$category',
            '$avatar.$id' => ['avatar_file_id'],
            '$cover.$id'  => ['cover_file_id'],
        ]);

        $this->remapLandingPage();

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
        $this->upsertBatchEntriesInChunked(GroupText::class, ['id']);
        $this->upsertBatchEntriesInChunked(UserEntity::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $oid  = $entry['$oid'];
        $name = html_entity_decode($entry['name'] ?? '');

        $this->addEntryToBatch(
            Model::class,
            array_merge(
                [
                    'id'                            => $oid,
                    'privacy'                       => $this->privacyMapEntry($entry),
                    'category_id'                   => $entry['category_id'] ?? 0,
                    'name'                          => $name,
                    'profile_name'                  => $entry['profile_name'] ?? null,
                    'phone'                         => $entry['phone'] ?? null,
                    'external_link'                 => $entry['external_link'] ?? null,
                    'landing_page'                  => $entry['landing_page'] ?? 'home',
                    'is_approved'                   => $entry['is_approved'] ?? 1,
                    'is_featured'                   => $entry['is_featured'] ?? 0,
                    'featured_at'                   => $entry['featured_at'] ?? null,
                    'is_sponsor'                    => $entry['is_sponsor'] ?? 0,
                    'sponsor_in_feed'               => $entry['sponsor_in_feed'] ?? 0,
                    'pending_mode'                  => $entry['pending_mode'] ?? 0,
                    'user_id'                       => $entry['user_id'] ?? null,
                    'user_type'                     => $entry['user_type'] ?? null,
                    'avatar_type'                   => isset($entry['avatar_file_id']) ? 'photo' : null,
                    'avatar_file_id'                => $entry['avatar_file_id'] ?? null,
                    'cover_type'                    => isset($entry['cover_file_id']) ? 'photo' : null,
                    'cover_file_id'                 => $entry['cover_file_id'] ?? null,
                    'cover_photo_position'          => $entry['cover_photo_position'] ?? null,
                    'updated_at'                    => $entry['updated_at'] ?? null,
                    'created_at'                    => $entry['created_at'] ?? null,
                    'deleted_at'                    => $entry['deleted_at'] ?? null,
                    'total_member'                  => $entry['total_member'] ?? 0,
                    'total_pending_post'            => $entry['total_pending_post'] ?? 0,
                    'location_latitude'             => $entry['location_latitude'] ?? null,
                    'location_longitude'            => $entry['location_longitude'] ?? null,
                    'location_name'                 => isset($entry['location_name']) ? html_entity_decode($entry['location_name']) : null,
                    'is_rule_confirmation'          => $entry['is_rule_confirmation'] ?? 1,
                    'is_answer_membership_question' => $entry['is_answer_membership_question'] ?? 0,
                ],
                $this->handlePrivacy($entry['privacy'] ?? 0)
            )
        );

        $this->addEntryToBatch(
            GroupText::class,
            [
                'id'          => $oid,
                'text'        => $entry['text'] ?? '',
                'text_parsed' => $this->parseText($entry['text_parsed'] ?? '', false),
            ]
        );

        $this->addEntryToBatch(UserEntity::class, [
            'id'             => $oid,
            'entity_type'    => Model::ENTITY_TYPE,
            'user_name'      => $entry['profile_name'] ?? null,
            'name'           => $name,
            'short_name'     => UserFacade::getShortName($name),
            'avatar_id'      => $entry['avatar_id'] ?? null,
            'avatar_type'    => isset($entry['avatar_file_id']) ? 'photo' : null,
            'avatar_file_id' => $entry['avatar_file_id'] ?? null,
            'is_featured'    => $entry['is_featured'] ?? 0,
            'is_searchable'  => $entry['is_searchable'] ?? 1,
            'gender'         => $entry['gender'] ?? 0,
            'deleted_at'     => $entry['deleted_at'] ?? null,
        ]);
    }

    private function handlePrivacy(?string $privacyType): array
    {
        if (!$privacyType || !in_array($privacyType, PrivacyTypeHandler::ALLOW_PRIVACY)) {
            $privacyType = 0;
        }

        $privacyHandler = resolve(PrivacyTypeHandler::class);

        return [
            'privacy_type' => $privacyType,
            'privacy'      => $privacyHandler->getPrivacy($privacyType),
            'privacy_item' => $privacyHandler->getPrivacyItem($privacyType),
        ];
    }

    public function privacyMap(int|string $privacy)
    {
        if (!$privacy || !in_array($privacy, PrivacyTypeHandler::ALLOW_PRIVACY)) {
            return 0;
        }

        return resolve(PrivacyTypeHandler::class)->getPrivacy($privacy);
    }
}
