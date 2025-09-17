<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class DbTableHelper.
 *
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
class DbTableHelper
{
    public const PRIVACY_COLUMN = 'privacy';

    public const PRIVACY_ID_COLUMN = 'privacy_id';

    /**
     * Create view_id column.
     *
     * @param Blueprint $table
     */
    public static function viewColumn(Blueprint $table): void
    {
        $table->unsignedTinyInteger('view_id')
            ->default(0)
            ->index();
    }

    /**
     * Create user_id and user_type columns.
     *
     * @param Blueprint $table
     * @param bool      $nullable
     */
    public static function morphUserColumn(Blueprint $table, bool $nullable = false): void
    {
        static::morphColumn($table, 'user', $nullable);
    }

    /**
     * Create owner_id and owner_type columns.
     *
     * @param Blueprint $table
     * @param bool      $nullable
     */
    public static function morphOwnerColumn(Blueprint $table, bool $nullable = false): void
    {
        static::morphColumn($table, 'owner', $nullable);
    }

    /**
     * Create nullable owner_id and owner_type columns.
     *
     * @param Blueprint $table
     *
     * @deprecated true
     * @ignore
     * @codeCoverageIgnore
     */
    public static function morphNullableOwnerColumn(Blueprint $table): void
    {
        static::morphColumn($table, 'owner', true);
    }

    /**
     * Create item_id and item_type columns.
     *
     * @param Blueprint $table
     * @param bool      $nullable
     */
    public static function morphItemColumn(Blueprint $table, bool $nullable = false): void
    {
        static::morphColumn($table, 'item', $nullable);
    }

    /**
     * Create item_id, item_type and type_id columns.
     *
     * @param Blueprint   $table
     * @param bool        $nullable
     * @param string|null $indexName
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public static function morphItemWithTypeColumn(
        Blueprint $table,
        bool $nullable = false,
        ?string $indexName = null
    ): void {
        self::morphItemColumn($table);

        $table->string('type_id')->nullable($nullable);

        $table->index(['item_id', 'item_type', 'type_id'], $indexName ?? "ix_{$table->getTable()}_item_type_morph");
    }

    public static function morphNullableItemColumn(Blueprint $table): void
    {
        static::morphColumn($table, 'item', true);
    }

    /**
     * Create morph columns.
     *
     * @param Blueprint   $table
     * @param string      $name
     * @param bool        $nullable
     * @param string|null $indexName
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public static function morphColumn(
        Blueprint $table,
        string $name,
        bool $nullable = false,
        ?string $indexName = null
    ): void {
        $table->unsignedBigInteger("{$name}_id")
            ->nullable($nullable);
        $table->string("{$name}_type")
            ->nullable($nullable);
        $table->index("{$name}_id");
        $table->index(["{$name}_id", "{$name}_type"], $indexName ?? "ix_{$table->getTable()}_{$name}_morph");
    }

    /**
     * Create morph column with index type.
     *
     * @param Blueprint   $table
     * @param string      $name
     * @param bool        $nullable
     * @param string|null $indexName
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public static function morphColumnIndexedType(
        Blueprint $table,
        string $name,
        bool $nullable = false,
        ?string $indexName = null
    ): void {
        self::morphColumn($table, $name, $nullable, $indexName);
        $table->index("{$name}_type");
    }

    /**
     * Create a privacy stream table.
     *
     * @param string $tableName
     */
    public static function setupPrivacyStreamTable(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->bigIncrements('id');
                self::privacyIdColumn($table);
                $table->unsignedBigInteger('item_id')->index();
            });
        }
    }

    /**
     * Create a network privacy stream table.
     *
     * @param string $tableName
     */
    public static function setupNetworkStreamTable(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('network_id')->index();
                $table->unsignedBigInteger('item_id')->index();
            });
        }
    }

    /**
     * Create both network and privacy stream table.
     *
     * @param string $tableName
     */
    public static function streamTables(string $tableName): void
    {
        static::setupNetworkStreamTable("{$tableName}_network_streams");
        static::setupPrivacyStreamTable("{$tableName}_privacy_streams");
    }

    /**
     * Drop both stream network and privacy stream privacy table.
     *
     * @param string $tableName
     */
    public static function dropStreamTables(string $tableName): void
    {
        Schema::dropIfExists("{$tableName}_network_streams");
        Schema::dropIfExists("{$tableName}_privacy_streams");
    }

    /**
     * Create integer columns.
     *
     * @param Blueprint $table
     * @param string[]  $columns Example ['like','comment','share'], Create 'total_like',
     *                           'total_comment','total_share' columns.
     */
    public static function totalColumns(Blueprint $table, array $columns = ['like', 'comment', 'share']): void
    {
        foreach ($columns as $key) {
            if (!trim($key)) {
                continue;
            }
            $key = preg_replace('/\s+/', '', $key);
            $table->unsignedInteger("total_{$key}")
                ->default(0);
        }
    }

    /**
     * Create country_iso, country_child_id, postal_code, city columns.
     *
     * @param Blueprint $table
     */
    public static function countryColumns(Blueprint $table): void
    {
        $table->string('country_iso', 5)->nullable();
        $table->unsignedInteger('country_child_id')->nullable();
        $table->string('postal_code', 50)->nullable();
        $table->string('city')->nullable();
    }

    /**
     * Create location_latitude, location_longitude and location_name columns.
     *
     * @param Blueprint $table
     */
    public static function locationColumn(Blueprint $table): void
    {
        $table->decimal('location_latitude', 30, 8)->nullable();

        $table->decimal('location_longitude', 30, 8)->nullable();

        $table->string('location_name')->nullable();

        /**
         * Check column exists to cover fresh site installation
         */
        if (!Schema::hasTable($table->getTable()) || !Schema::hasColumn($table->getTable(), 'location_address')) {
            static::locationAddressColumn($table);
        }
    }

    public static function locationAddressColumn(Blueprint $table, string $name = 'location_address', ?string $after = null): void
    {
        $column = $table->text($name)->nullable();

        if (null === $after) {
            return;
        }

        $column->after($after);
    }

    /**
     * Create `content` text column.
     *
     * @param Blueprint $table
     */
    public static function feedContentColumn(Blueprint $table): void
    {
        $table->text('content')->nullable();
    }

    /**
     * Create module_id column.
     *
     * @param Blueprint $table
     * @param bool      $nullable
     */
    public static function moduleColumn(Blueprint $table, bool $nullable = true): void
    {
        $table->string('module_id')
            ->nullable($nullable)
            ->index();

        $table->string('package_id')
            ->nullable($nullable)
            ->index();
    }

    /**
     * Create entity_type column.
     *
     * @param Blueprint $table
     * @param bool      $nullable
     */
    public static function entityTypeColumn(Blueprint $table, bool $nullable = true): void
    {
        $table->string('entity_type')
            ->nullable($nullable)
            ->index();
    }

    /**
     * Create is_public column.
     *
     * @param Blueprint $table
     * @param int       $defaultValue
     */
    public static function publicColumn(Blueprint $table, int $defaultValue = 1): void
    {
        $table->tinyInteger('is_public')
            ->default($defaultValue)
            ->index();
    }

    /**
     * Create is_sponsor and sponsor_in_feed columns.
     *
     * @param Blueprint $table
     */
    public static function sponsorColumn(Blueprint $table): void
    {
        $table->unsignedTinyInteger('is_sponsor')
            ->default(0)
            ->index();

        $table->unsignedTinyInteger('sponsor_in_feed')
            ->default(0);
    }

    /**
     * Create is_featured and featured_at columns.
     *
     * @param Blueprint $table
     */
    public static function featuredColumn(Blueprint $table): void
    {
        $table->unsignedTinyInteger('is_featured')
            ->default(0)
            ->index();

        $table->timestamp('featured_at')
            ->nullable();
    }

    /**
     * Create columns to describe image.
     *
     * @param Blueprint $table
     * @param string    $columnPrefix Example `photo`, create columns photo_id, photo_type
     */
    public static function morphImage(Blueprint $table, string $columnPrefix): void
    {
        $table->unsignedBigInteger("{$columnPrefix}_id")->nullable();
        $table->string("{$columnPrefix}_type")->nullable();
        static::imageColumns($table, "{$columnPrefix}_file_id");

        $table->index("{$columnPrefix}_id");
        $table->index(["{$columnPrefix}_id", "{$columnPrefix}_type"]);
    }

    /**
     * Create image_path and server_id columns.
     *
     * @param Blueprint $table
     * @param string    $fileIdColumn
     */
    public static function imageColumns(
        Blueprint $table,
        string $fileIdColumn = 'image_file_id',
    ): void {
        $table->unsignedBigInteger($fileIdColumn)->nullable();
    }

    /**
     * Create is_approved column.
     *
     * @param Blueprint $table
     */
    public static function approvedColumn(Blueprint $table): void
    {
        $table->unsignedTinyInteger('is_approved')
            ->default(1)
            ->index();
    }

    /**
     * Create a privacy column.
     *
     * @param Blueprint $table
     */
    public static function privacyColumn(Blueprint $table): void
    {
        $table->unsignedTinyInteger(self::PRIVACY_COLUMN)
            ->default(0)
            ->index();
    }

    /**
     * create a privacy_id column.
     *
     * @param Blueprint $table
     * @param bool      $nullable Optional, default=false
     */
    public static function privacyIdColumn(Blueprint $table, bool $nullable = false): void
    {
        $table->unsignedBigInteger(self::PRIVACY_ID_COLUMN)->nullable($nullable)->index();
    }

    /**
     * Create privacy_type and privacy_item column.
     *
     * @param Blueprint $table
     */
    public static function privacyTypeColumn(Blueprint $table): void
    {
        $table->tinyInteger('privacy_type')->default(0)->index();
        $table->tinyInteger('privacy_item')->default(0)->index();
    }

    /**
     * Setup user, owner, item, privacy, like, comment, share for a table.
     *
     * @param Blueprint $table
     * @param bool      $user
     * @param bool      $owner
     * @param bool      $privacy
     * @param bool      $module
     */
    public static function setupResourceColumns(
        Blueprint $table,
        bool $user,
        bool $owner,
        bool $privacy,
        bool $module
    ): void {
        if ($module) {
            static::moduleColumn($table);
        }

        if ($user) {
            static::morphColumn($table, 'user');
        }

        if ($owner) {
            static::morphColumn($table, 'owner');
        }

        if ($privacy) {
            self::privacyColumn($table);
        }
    }

    /**
     * Create *_category_data table.
     *
     * @param string $tableName
     * @param string $parentName
     */
    public static function categoryDataTable(string $tableName, string $parentName = 'category_id'): void
    {
        if (!Schema::hasTable($tableName)) {
            Schema::create(
                $tableName,
                function (Blueprint $table) use ($parentName) {
                    $table->bigIncrements('id');
                    $table->unsignedBigInteger('item_id')->index();
                    $table->unsignedInteger($parentName)->index();
                }
            );
        }
    }

    /**
     * Create *_tag_data table.
     *
     * @param string $tableName
     */
    public static function createTagDataTable(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('item_id')
                    ->index();
                $table->unsignedInteger('tag_id')
                    ->nullable(true);
                $table->unique(['item_id', 'tag_id']);
            });
        }
    }

    /**
     * Create category table.
     *
     * @param string     $tableName
     * @param bool|false $parentId
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public static function categoryTable(string $tableName, bool $parentId = false, bool $level = false): void
    {
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($parentId, $level) {
                $table->integerIncrements('id');
                if ($parentId) {
                    $table->unsignedInteger('parent_id')
                        ->nullable();
                }
                if ($level) {
                    $table->unsignedInteger('level')->default(1);
                }
                $table->string('name');
                $table->string('name_url')->nullable()->index();
                $table->unsignedTinyInteger('is_active')
                    ->default(1);
                $table->unsignedInteger('ordering')
                    ->default(0);
                $table->unsignedInteger('total_item')
                    ->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Create *_text table.
     *
     * @param string $tableName
     */
    public static function textTable(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('id')->primary();
                $table->mediumText('text');
                $table->mediumText('text_parsed');
            });
        }
    }

    /**
     * Create full_text index.
     *
     * @param string        $table
     * @param string        $column
     * @param array<string> $columns
     */
    public static function createFullTextIndex(string $table, string $column, array $columns)
    {
        $driver = database_driver();
        $table  = DB::getTablePrefix() . $table;

        if ($driver == 'pgsql') {
            $cols = implode(',', array_map(function ($name) {
                return "'$name'";
            }, $columns));
            DB::statement("ALTER TABLE {$table} ADD COLUMN {$column} TSVECTOR");
            DB::statement("CREATE INDEX {$column}_gin ON {$table} USING GIN({$column})");
            DB::statement("CREATE TRIGGER ts_{$column} BEFORE INSERT OR UPDATE ON {$table} FOR EACH ROW EXECUTE PROCEDURE tsvector_update_trigger('{$column}', 'pg_catalog.english', $cols)");
        }

        if ($driver == 'mysql') {
            $cols = implode(',', array_map(function ($name) {
                return "`$name`";
            }, $columns));
            DB::statement("ALTER TABLE `{$table}` ADD FULLTEXT ($cols)");
        }
    }

    /**
     * Drop full_text index.
     *
     * @param string $table
     * @param string $column
     */
    public static function dropFullTextIndex(string $table, string $column)
    {
        $driver = database_driver();

        if ($driver === 'pgsql') {
            DB::statement("DROP TRIGGER IF EXISTS tsvector_update_trigger ON {$table}");
            DB::statement("DROP INDEX IF EXISTS {$column}_gin");
            DB::statement("ALTER TABLE {$table} DROP COLUMN {$column}");
        }
    }

    /**
     * Create text and text_parsed columns.
     *
     * @param Blueprint $table
     */
    public static function resourceTextColumns(Blueprint $table)
    {
        $table->mediumText('text');
        $table->mediumText('text_parsed');
    }

    /**
     * Create tags column, medium text.
     *
     * @param Blueprint $table
     * @param bool      $nullable
     *
     * @return ColumnDefinition
     */
    public static function tagsColumns(Blueprint $table, bool $nullable = true): ColumnDefinition
    {
        return $table->mediumText('tags')->nullable($nullable);
    }

    /**
     * Add is_admin column, unsigned tiny integer default =0.
     * @param Blueprint $table
     * @param int       $defaultValue
     *
     * @return ColumnDefinition
     */
    public static function adminColumn(Blueprint $table, int $defaultValue = 0): ColumnDefinition
    {
        return $table->unsignedTinyInteger('is_admin')->default($defaultValue);
    }

    /**
     * Add "is_active" column, type=unsigned tiny integer.
     *
     * @param Blueprint $table
     * @param int       $defaultValue
     *
     * @return ColumnDefinition
     */
    public static function activeColumn(Blueprint $table, int $defaultValue = 1): ColumnDefinition
    {
        return $table->unsignedTinyInteger('is_active')->default($defaultValue);
    }

    /**
     * Add a pair of column which represents the item pricing and currency.
     *
     * @param Blueprint $table
     * @param string    $column
     *
     * @return void
     */
    public static function pricingColumns(Blueprint $table, string $column = 'price'): void
    {
        $table->double($column)->default(0);
        $table->string('currency', 3)->default('USD');
    }

    public static function aggreateManualModified(
        string $modelClass,
        string $modifiedColumn = 'is_modified',
        string $updatedColumn = 'updated_at'
    ) {
        $column = DB::raw('count(id)');

        if (!class_exists($modelClass)) {
            return -1;
        }

        /** @var Model $modelInstance */
        $modelInstance = resolve($modelClass);

        if (!$modelInstance instanceof Model) {
            return -2;
        }

        $array = $modelInstance::query()
            ->select([$column, $updatedColumn])
            ->groupBy([$updatedColumn])
            ->having($column, '=', '1')
            ->pluck($updatedColumn)
            ->toArray();

        $chunks = array_chunk($array, 20);

        // pluck user updated at.
        foreach ($chunks as $chunk) {
            $modelInstance::query()
                ->whereIn($updatedColumn, $chunk)
                ->where($modifiedColumn, '=', 0)
                ->update([$modifiedColumn => 1]);
        }

        return count($array);
    }

    public static function deleteDuplicatedRows(string $tableName, string $primaryKey, array $uniqueColumns)
    {
        $raw = DB::table($tableName)
            ->select(DB::raw("min($primaryKey) as $primaryKey"))
            ->groupBy($uniqueColumns)
            ->toSql();

        return DB::statement("DELETE FROM $tableName WHERE $primaryKey NOT IN ( SELECT $primaryKey FROM (" . $raw . ') as foo)');
    }

    public static function getDatabaseSize()
    {
        if (DB::getDriverName() === 'mysql') {
            $result = DB::select(DB::raw('SELECT table_name AS "Table"
                FROM information_schema.TABLES
                WHERE table_schema ="' . DB::getDatabaseName() . '"
                ORDER BY (data_length + index_length) DESC')->getValue(DB::getQueryGrammar()));

            return array_sum(array_column($result, 'Size'));
        }

        if (DB::getDriverName() === 'pgsql') {
            return DB::selectOne('select pg_database_size(\'' . DB::getDatabaseName() . '\');')->pg_database_size;
        }

        return 0;
    }

    public static function getDriverVersion()
    {
        return DB::getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);
    }

    /**
     * model must contain fillable $column.
     * @param  Model  $model
     * @param  string $column
     * @return bool
     */
    public static function addMisingTotalColumn(Model $model, string $column)
    {
        if (!in_array($column, $model->getFillable())) {
            return false;
        }

        if (!Schema::hasTable($model->getTable())
            || Schema::hasColumn($model->getTable(), $column)) {
            return false;
        }

        Schema::table($model->getTable(), function (Blueprint $table) use ($column) {
            $table->unsignedInteger($column)->default(0);
        });

        return true;
    }

    public static function migrateCounter(
        Model $model,
        string $updateColumn,
        Builder $query,
        string $primaryKey,
        string $foreignKey,
        bool $dryrun
    ) {
        $table = $model->getTable();

        $driver = DB::getDriverName();
        $rawSql = null;

        if ($driver == 'pgsql') {
            $rawSql = sprintf(
                'with A as (%s) update %s set %s = A.aggregate from A where %s = A.%s;',
                $query->toSql(),
                $table,
                $updateColumn,
                $primaryKey,
                $foreignKey
            );
        }

        if ($driver == 'mysql') {
            $rawSql = sprintf(
                'update `%s` A inner join (%s) as B on A.`%s`=B.`%s` set A.`%s` = B.`aggregate` where 1;',
                $table,
                $query->toSql(),
                $primaryKey,
                $foreignKey,
                $updateColumn,
            );
        }

        if ($dryrun) {
            echo $rawSql;
        }

        if (!$dryrun) {
            DB::statement($rawSql, $query->getBindings());
        }
    }

    public static function migrateCoreItemStatisticCounter(
        string $column,
        string $insertSql,
        string $updateSql,
        bool $dryrun
    ) {
        $driver          = DB::getDriverName();
        $table           = 'core_item_statistics';
        $rawSqlForInsert = null;
        $rawSqlForUpdate = null;

        if ('mysql' == $driver) {
            $rawSqlForInsert = sprintf(
                'insert ignore into %s (item_id, item_type) %s;',
                $table,
                $insertSql
            );
            $rawSqlForUpdate = sprintf(
                'update %s A inner join (%s) as B on (B.item_id = A.item_id and B.item_type=A.item_type)
    set A.%s = B.aggregate where 1;',
                $table,
                $updateSql,
                $column
            );

            if ($dryrun && $insertSql) {
                echo '# mysql for insert', PHP_EOL;
                echo "$rawSqlForInsert", PHP_EOL, PHP_EOL;
            }

            if ($dryrun && $updateSql) {
                echo '# mysql for update', PHP_EOL;
                echo "$rawSqlForUpdate", PHP_EOL, PHP_EOL;
            }
        }

        if ('pgsql' == $driver) {
            $rawSqlForInsert = sprintf(
                'insert into %s (item_id, item_type) %s on conflict(item_id) do nothing;',
                $table,
                $insertSql
            );

            $rawSqlForUpdate = sprintf(
                'with B as (%s) update %s as A
                    set %s = B.aggregate
                    from B where B.item_id = A.item_id and B.item_type = A.item_type;',
                $updateSql,
                $table,
                $column
            );

            if ($dryrun && $insertSql) {
                echo '--- pgsql for insert', PHP_EOL;
                echo "$rawSqlForInsert", PHP_EOL, PHP_EOL;
            }

            if ($dryrun && $updateSql) {
                echo '--- pgsql for update', PHP_EOL;
                echo "$rawSqlForUpdate", PHP_EOL, PHP_EOL;
            }
        }

        if ($dryrun) {
            return;
        }

        if ($insertSql) {
            DB::statement($rawSqlForInsert);
        }

        if ($updateSql) {
            DB::statement($rawSqlForUpdate);
        }
    }
}
