<?php

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
        if (!Schema::hasColumn('newsletter_text', 'text')) {
            Schema::table('newsletter_text', function (Blueprint $table) {
                $table->mediumText('text')->nullable();
            });
        }

        if (!Schema::hasColumn('newsletters', 'channels')) {
            Schema::table('newsletters', function (Blueprint $table) {
                $table->string('channels')->default(json_encode(["mail" => 1]));
            });
        }

        if (Schema::hasColumn('newsletter_text', 'text_html')) {
            Schema::table('newsletter_text', function (Blueprint $table) {
                $table->mediumText('text_html')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('newsletter_text', function (Blueprint $table) {
            $table->dropColumn('text');
        });

        Schema::table('newsletters', function (Blueprint $table) {
            $table->dropColumn('channels');
        });
    }
};
