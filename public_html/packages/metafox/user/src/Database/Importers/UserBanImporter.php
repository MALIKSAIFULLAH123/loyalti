<?php

namespace MetaFox\User\Database\Importers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use MetaFox\Authorization\Models\Role;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User;
use MetaFox\User\Models\UserBan as Model;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

/*
 * stub: packages/database/json-importer.stub
 */

class UserBanImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'start_time_stamp',
        'end_time_stamp',
        'reason',
        'ban_id',
        'created_at',
        'updated_at',
    ];

    // fill from data to model refs.
    protected $relations = [
        'owner',
        'return_user_group',
    ];

    protected array $requiredColumns = [
        'owner_id',
        'return_user_group',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function saving($model, array $data, array $relations, string $source): void
    {
        if (!$model instanceof Model) {
            throw new \RuntimeException(sprintf('Failed importing data %s', __CLASS__));
        }

        $owner = Arr::get($relations, 'owner');
        if (!$owner instanceof User) {
            throw new \RuntimeException(sprintf('Failed importing data %s:%s - Missing owner relation', __CLASS__,
                $data['$id']));
        }

        $returnUserGroup = Arr::get($relations, 'return_user_group');
        if (!$returnUserGroup instanceof Role) {
            throw new \RuntimeException(sprintf('Failed importing data %s:%s - Missing return_user_group relation',
                __CLASS__, $data['$id']));
        }

        $user = $this->getDefaultUser();

        // handle saving logic

        $model->owner_id = $owner->entityId();
        $model->owner_type = $owner->entityType();
        $model->user_id = $user->entityId();
        $model->user_type = $user->entityType();
        $model->return_user_group = $returnUserGroup->entityId();
    }

    private function getDefaultUser(): User
    {
        return Cache::remember('default_user', 3600, function () {
            return resolve(UserRepositoryInterface::class)->getUserByRoleId(UserRole::SUPER_ADMIN_USER);
        });
    }

    public function processImport()
    {
        $this->remapRefs(['$owner' => ['owner_id', 'owner_type'], '$return_user_group' => ['return_user_group']]);
        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'                => $entry['$oid'],
            'start_time_stamp'  => $entry['start_time_stamp'] ?? null,
            'end_time_stamp'    => $entry['end_time_stamp'] ?? null,
            'return_user_group' => $entry['return_user_group'] ?? null,
            'reason'            => $entry['reason'] ?? null,
            'owner_id'          => $entry['owner_id'] ?? null,
            'owner_type'        => $entry['owner_type'] ?? null,
            'user_id'           => 1,
            'user_type'         => 'user',
            'ban_id'            => $entry['ban_id'] ?? null,
            'created_at'        => $entry['created_at'] ?? null,
            'updated_at'        => $entry['updated_at'] ?? null,
        ]);
    }
}
