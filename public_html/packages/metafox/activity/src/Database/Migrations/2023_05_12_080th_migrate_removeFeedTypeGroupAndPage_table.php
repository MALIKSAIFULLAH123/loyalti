<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        \MetaFox\Activity\Models\Type::query()
            ->where([
                'type'      => 'group',
                'module_id' => 'group',
            ])
            ->delete();

        \MetaFox\Activity\Models\Type::query()
            ->where([
                'type'      => 'page',
                'module_id' => 'page',
            ])
            ->delete();

        \MetaFox\Activity\Models\Type::query()
            ->where([
                'type'      => 'test',
                'module_id' => 'core',
            ])
            ->delete();

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('removeFeedTypeGroupAndPage');
    }
};
