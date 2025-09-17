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
        $this->addPhotoTagData();
        $this->addPhotoAlbumTagData();
    }

    protected function addPhotoTagData(): void
    {
        if (Schema::hasTable('photo_tag_data')) {
            return;
        }

        DbTableHelper::createTagDataTable('photo_tag_data');
    }

    protected function addPhotoAlbumTagData(): void
    {
        if (Schema::hasTable('photo_album_tag_data')) {
            return;
        }

        DbTableHelper::createTagDataTable('photo_album_tag_data');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_tag_data');
        Schema::dropIfExists('photo_album_tag_data');
    }
};
