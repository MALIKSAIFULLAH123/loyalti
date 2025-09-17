<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $model = new MetaFox\Group\Models\Group();

        $this->addTotalInvite($model);
        $this->addTotalAdmin($model);
        $this->addTotalPendingRequest($model);
        $this->addTotalQuestion($model);
        $this->addTotalRule($model);
    }

    public function addTotalInvite(Illuminate\Database\Eloquent\Model $model)
    {
        $updateColumn = 'total_invite';
        if (!DbTableHelper::addMisingTotalColumn($model, $updateColumn)) {
            return;
        }

        $query = \MetaFox\Group\Models\Invite::query()
            ->selectRaw('group_id, count(*) as aggregate')
            ->groupBy('group_id');

        DbTableHelper::migrateCounter($model, $updateColumn, $query, 'id', 'group_id', false);
    }

    public function addTotalQuestion(Illuminate\Database\Eloquent\Model $model)
    {
        $updateColumn = 'total_question';
        if (!DbTableHelper::addMisingTotalColumn($model, $updateColumn)) {
            return;
        }

        $query = \MetaFox\Group\Models\Question::query()
            ->selectRaw('group_id, count(*) as aggregate')
            ->groupBy('group_id');

        DbTableHelper::migrateCounter($model, $updateColumn, $query, 'id', 'group_id', false);
    }

    public function addTotalRule(Illuminate\Database\Eloquent\Model $model)
    {
        $updateColumn = 'total_rule';
        if (!DbTableHelper::addMisingTotalColumn($model, $updateColumn)) {
            return;
        }

        $query = \MetaFox\Group\Models\Rule::query()
            ->selectRaw('group_id, count(*) as aggregate')
            ->groupBy('group_id');

        DbTableHelper::migrateCounter($model, $updateColumn, $query, 'id', 'group_id', false);
    }

    public function addTotalPendingRequest(Illuminate\Database\Eloquent\Model $model)
    {
        $updateColumn = 'total_pending_request';
        if (!DbTableHelper::addMisingTotalColumn($model, $updateColumn)) {
            return;
        }

        $query = \MetaFox\Group\Models\Request::query()
            ->selectRaw('group_id, count(*) as aggregate')
            ->groupBy('group_id');

        DbTableHelper::migrateCounter($model, $updateColumn, $query, 'id', 'group_id', false);
    }

    public function addTotalAdmin(Illuminate\Database\Eloquent\Model $model)
    {
        $updateColumn = 'total_admin';
        if (!DbTableHelper::addMisingTotalColumn($model, $updateColumn)) {
            return;
        }

        $query = \MetaFox\Group\Models\Member::query()
            ->selectRaw('group_id, count(*) as aggregate')
            ->where(['member_type' => \MetaFox\Group\Models\Member::ADMIN])
            ->groupBy('group_id');

        DbTableHelper::migrateCounter($model, $updateColumn, $query, 'id', 'group_id', false);
    }
};
