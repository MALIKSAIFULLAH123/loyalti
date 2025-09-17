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
        $model = new MetaFox\Page\Models\Page();

        $this->addTotalInvite($model);
        $this->addTotalAdmin($model);
    }

    public function addTotalInvite(Illuminate\Database\Eloquent\Model $model)
    {
        $updateColumn = 'total_invite';
        if (!DbTableHelper::addMisingTotalColumn($model, $updateColumn)) {
            return;
        }

        $query = \MetaFox\Page\Models\PageInvite::query()
            ->selectRaw('page_id, count(*) as aggregate')
            ->groupBy('page_id');

        DbTableHelper::migrateCounter($model, $updateColumn, $query, 'id', 'page_id', false);
    }

    public function addTotalAdmin(Illuminate\Database\Eloquent\Model $model)
    {
        $updateColumn = 'total_admin';
        if (!DbTableHelper::addMisingTotalColumn($model, $updateColumn)) {
            return;
        }

        $query = \MetaFox\Page\Models\PageMember::query()
            ->selectRaw('page_id, count(*) as aggregate')
            ->where(['member_type' => \MetaFox\Page\Models\PageMember::ADMIN])
            ->groupBy('page_id');

        DbTableHelper::migrateCounter($model, $updateColumn, $query, 'id', 'page_id', false);
    }
};
