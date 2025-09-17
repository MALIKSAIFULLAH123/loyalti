<?php

namespace MetaFox\User\Database\Importers;

use Illuminate\Support\Arr;
use MetaFox\Core\Models\Privacy;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Models\UserActivity;
use MetaFox\User\Models\UserEntity;
use MetaFox\User\Models\UserHasRoles;
use MetaFox\User\Models\UserPassword;
use MetaFox\User\Models\UserProfile;

/*
 * stub: packages/database/json-importer.stub
 */

class UserImporter extends JsonImporter
{
    private array $passwordMethod = [
        'phpfox' => 'MetaFox\User\Password\v4Password',
    ];

    protected array $uniqueColumns = ['user_name', 'email'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    // batch to raw query in database.
    public function processImport()
    {
        $this->remapRefs([
            '$owner' => ['owner_id', 'owner_type'],
            '$user'  => ['user_id', 'user_type'],
            '$role'  => ['role_id'],
        ]);

        $this->loadExistsIdFromUniqueColumns();
        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['user_name']);
        $this->upsertBatchEntriesInChunked(UserHasRoles::class, ['model_id', 'model_type', 'role_id']);
        $this->upsertBatchEntriesInChunked(UserPassword::class, ['user_id']);
        $this->upsertBatchEntriesInChunked(UserEntity::class, ['id']);
        $this->upsertBatchEntriesInChunked(UserProfile::class, ['id']);
        $this->upsertBatchEntriesInChunked(UserActivity::class, ['id']);
        $this->insertBatchEntriesInChunked(Privacy::class);
    }

    public function processImportEntry(array &$entry): void
    {
        if (!empty($entry['data_exist'])) {
            return;
        }
        // check duplicated by user_name or email or vise versa.
        $oid    = $entry['$oid'];
        $source = $this->bundle->source;

        $this->addEntryToBatch(Model::class, [
            'id'                       => $oid,
            'user_name'                => $entry['user_name'],
            'full_name'                => $entry['full_name'],
            'first_name'               => $entry['first_name'] ?? null,
            'last_name'                => $entry['last_name'] ?? null,
            'password'                 => '', // Use UserPassword to store password
            'email'                    => $entry['email'] ?? '',
            'phone_number'             => $entry['phone_number'] ?? null,
            'is_featured'              => $entry['is_featured'] ?? 0,
            'is_invisible'             => $entry['is_invisible'] ?? 0,
            'approve_status'           => $this->processApproveStatus($entry),
            'updated_at'               => $entry['updated_at'] ?? null,
            'email_verified_at'        => $entry['email_verified_at'] ?? null,
            'phone_number_verified_at' => $entry['phone_number_verified_at'] ?? null,
            'verified_at'              => $entry['email_verified_at'] ?? null,
            'remember_token'           => $entry['remember_token'] ?? null,
            'created_at'               => $entry['created_at'] ?? null,
            'featured_at'              => $entry['featured_at'] ?? null,
            'search_name'              => $entry['full_name'] ?? $entry['user_name'],
        ]);

        $this->addEntryToBatch(UserEntity::class, [
            'id'             => $oid,
            'entity_type'    => Model::ENTITY_TYPE,
            'user_name'      => $entry['user_name'] ?? null,
            'name'           => $entry['full_name'] ?? '',
            'short_name'     => $entry['short_name'] ?? '',
            'avatar_id'      => $entry['avatar_id'] ?? null,
            'avatar_type'    => $entry['avatar_type'] ?? null,
            'avatar_file_id' => $entry['avatar_file_id'] ?? null,
            'is_featured'    => $entry['is_featured'] ?? 0,
            'is_searchable'  => $entry['is_searchable'] ?? 1,
            'gender'         => $entry['gender'] ?? 0,
            'deleted_at'     => $entry['deleted_at'] ?? null,
        ]);

        $this->addEntryToBatch(UserPassword::class, [
            'user_id'         => $oid,
            'password_hash'   => $entry['password'] ?? '',
            'password_salt'   => $entry['password_salt'] ?? '',
            'password_method' => Arr::get($this->passwordMethod, $source),
            'params'          => '[]',
        ]);

        $this->addEntryToBatch(UserHasRoles::class, [
            'model_id'   => $oid,
            'role_id'    => $entry['role_id'] ?? UserRole::NORMAL_USER_ID,
            'model_type' => 'user',
        ]);

        // prevent error after import
        $this->addEntryToBatch(UserProfile::class, [
            'id' => $oid,
        ]);

        $this->addEntryToBatch(UserActivity::class, [
            'id' => $oid,
        ]);
    }

    public function afterPrepare(): void
    {
        $this->appendFileBundle('$avatar');
        $this->appendFileBundle('$cover');
        $this->transformPrivacyList(MetaFoxPrivacy::ONLY_ME, 'user_private', '$id', '$id');
        $this->transformPrivacyList(MetaFoxPrivacy::FRIENDS, 'user_friends', '$id', '$id');
        $this->transformPrivacyMember([MetaFoxPrivacy::EVERYONE, MetaFoxPrivacy::MEMBERS, MetaFoxPrivacy::FRIENDS_OF_FRIENDS, MetaFoxPrivacy::FRIENDS, MetaFoxPrivacy::ONLY_ME], '$id');
        $this->transformActivitySubscription('$id', '$id', true);
    }

    protected function processApproveStatus(array $entry): string
    {
        if (isset($entry['approve_status'])
            && in_array($entry['approve_status'], [
                MetaFoxConstant::STATUS_APPROVED,
                MetaFoxConstant::STATUS_PENDING_APPROVAL,
                MetaFoxConstant::STATUS_NOT_APPROVED,
            ])
        ) {
            return $entry['approve_status'];
        }

        return !empty($entry['is_approved']) ? MetaFoxConstant::STATUS_APPROVED
            : MetaFoxConstant::STATUS_PENDING_APPROVAL;
    }
}
