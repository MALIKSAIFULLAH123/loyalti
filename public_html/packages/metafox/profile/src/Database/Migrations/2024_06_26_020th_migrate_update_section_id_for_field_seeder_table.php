<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Models\Section;

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
        $section      = Section::query()->where('name', 'about')->first();
        $sectionTable = (new Section())->getTable();
        $fieldTable   = (new Field())->getTable();

        if (!$section instanceof Section) {
            return;
        }

        Field::query()->select("$fieldTable.*")
            ->leftJoin($sectionTable, function (JoinClause $joinClause) use ($sectionTable, $fieldTable) {
                $joinClause->on("$sectionTable.id", '=', "$fieldTable.section_id");
            })
            ->where("$sectionTable.is_system", 1)
            ->update([
                'section_id' => $section->entityId(),
            ]);

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
