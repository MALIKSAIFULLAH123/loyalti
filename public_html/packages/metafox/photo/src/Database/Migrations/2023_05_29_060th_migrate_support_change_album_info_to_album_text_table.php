<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Photo\Models\AlbumText;
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
        if (Schema::hasTable('photo_album_text')) {
            return;
        }

        if (!Schema::hasTable('photo_album_info')) {
            return;
        }

        Schema::table('photo_album_info', function (Blueprint $table) {
            $table->renameColumn('description', 'text');
            $table->mediumText('text_parsed')->nullable();
        });

        Schema::rename('photo_album_info', 'photo_album_text');

        $this->migrateTextParsedData();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }

    protected function migrateTextParsedData(): void
    {
        AlbumText::query()->chunkById(100, function ($records) {
            foreach ($records as $albumText) {
                if (!$albumText instanceof AlbumText) {
                    continue;
                }

                AlbumText::query()->where('id', $albumText->entityId())
                    ->update([
                        'text_parsed' => htmlspecialchars_decode($albumText->text, ENT_NOQUOTES),
                    ]);
            }
        });
    }
};
