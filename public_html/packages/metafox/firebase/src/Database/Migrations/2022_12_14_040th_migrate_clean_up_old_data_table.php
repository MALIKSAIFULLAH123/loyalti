<?php

use MetaFox\Core\Repositories\Eloquent\DriverRepository;
use Illuminate\Database\Migrations\Migration;

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
            'type'       => 'resource-mobile',
            'name'       => 'device',
            'resolution' => 'mobile',
        ],
        [
            'type'       => 'resource-web',
            'name'       => 'device',
            'resolution' => 'web',
        ],
        [
            'type'       => 'data-grid',
            'name'       => 'firebase.device',
            'resolution' => 'admin',
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
        $repository = resolve(DriverRepository::class);
        foreach ($this->obsoleteDrivers as $driver) {
            $repository->getModel()
                ->newModelQuery()
                ->where('package_id', '=', 'metafox/firebase')
                ->where($driver)
                ->delete();
        }
    }
};
