<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Core\Models\Driver;

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
     * @var array<int, array<string>>
     */
    private array $obsoleteDrivers = [
        [
            'type'       => 'form',
            'name'       => 'search.search_hashtag',
            'resolution' => 'mobile',
        ],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->cleanUpDrivers();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }

    private function cleanUpDrivers(): void
    {
        foreach ($this->obsoleteDrivers as $driver) {
            Driver::query()
                ->where('package_id', '=', 'metafox/search')
                ->where($driver)
                ->delete();
        }
    }
};
