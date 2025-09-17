<?php

namespace MetaFox\Authorization\Database\Importers;

use Illuminate\Support\Arr;
use MetaFox\Authorization\Models\Role;
use MetaFox\Authorization\Models\Role as Model;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Platform\UserRole;

/*
 * stub: packages/database/json-importer.stub
 */

class RoleImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'name',
        'guard_name',
        'is_special',
        'created_at',
        'updated_at',
        'parent_id',
    ];

    protected $roleMapping = [
        '1' => UserRole::ADMIN_USER_ID, // Administrator
        '2' => UserRole::NORMAL_USER_ID, // Registered user
        '3' => UserRole::GUEST_USER_ID, // Guest user
        '4' => UserRole::STAFF_USER_ID, // Staff
        '5' => UserRole::BANNED_USER_ID, // Banned
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function saved($model, array $data, array $relations, string $source): void
    {
        if (!$model instanceof Model) {
            throw new \RuntimeException(sprintf('Failed importing data %s', __CLASS__));
        }
        // handle saved logic
        if (!empty($relations)) {
            foreach ($relations as $relation) {
                if ($relation instanceof Model) {
                    /** @var Model $parentRole */
                    $parentRole = Role::query()->with(['permissions'])
                        ->where('id', '=', $relation?->id)
                        ->first();
                    if ($parentRole) {
                        $model->parent_id = $parentRole->entityId();
                        $model->save();
                        $model->givePermissionTo($parentRole->permissions);
                    }
                }
            }
        }
    }

    public function processImport()
    {
        $this->remapRefs([
            '$parent',
        ]);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $source    = $this->bundle->source;
        $isSpecial = $source == 'phpfox' && isset($this->roleMapping[$entry['$oid']]);
        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'],
            'name'       => $entry['name'],
            'guard_name' => $entry['guard_name'] ?? 'api',
            'is_special' => $entry['is_special'] ?? (int) $isSpecial,
            'created_at' => $entry['created_at'] ?? now(),
            'updated_at' => $entry['updated_at'] ?? now(),
            'parent_id'  => $entry['parent_id'] ?? 0,
        ]);
    }

    public function afterImport(): void
    {
        // Grant permission for child roles
        foreach ($this->entries as $entry) {
            if (empty($entry['parent_id'])) {
                continue;
            }
            $parentRole = Model::query()->with(['permissions'])
                ->where('id', '=', $entry['parent_id'])
                ->first();
            if ($parentRole) {
                $model = Model::query()->where('id', $entry['$oid'])->first();
                $model->save();
                $model->givePermissionTo($parentRole->permissions);
            }
        }
    }

    protected function loadPreservedIds()
    {
        $total    = 0;
        $resource = $this->bundle->resource;
        foreach ($this->entries as $entry) {
            if ($entry['$oid'] ?? null) {
                continue;
            }
            $total++;
        }

        $start  = $this->nextIdForResource($resource, $total);
        $source = $this->bundle->source;
        foreach ($this->entries as &$entry) {
            // Find existed role
            $explodeId = explode('#', $entry['$id']);
            $id        = Arr::get($explodeId, 1);
            $oldId     = null;
            if (str_contains($source, 'phpfox')) {
                $oldId = $this->roleMapping[$id] ?? null;
            }
            if ($entry['$oid'] ?? null) {
                continue;
            }
            $entry['$oid'] = $oldId ?? $start++;
        }
    }
}
