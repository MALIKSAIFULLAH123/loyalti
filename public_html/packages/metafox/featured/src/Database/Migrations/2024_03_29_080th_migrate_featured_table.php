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
        $this->createItem();
        $this->createInvoice();
        $this->createTransaction();
        $this->createPackage();
        $this->createApplicableUserRole();
        $this->createApplicableItemType();
    }

    private function createApplicableUserRole(): void
    {
        if (Schema::hasTable('featured_applicable_roles')) {
            return;
        }

        Schema::create('featured_applicable_roles', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('package_id', false, true)
                ->index('far_package_id');

            $table->bigInteger('role_id', false, true)
                ->index('far_role_id');

            $table->unique(['package_id', 'role_id']);
        });
    }

    private function createApplicableItemType(): void
    {
        if (Schema::hasTable('featured_applicable_item_types')) {
            return;
        }

        Schema::create('featured_applicable_item_types', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('package_id', false, true)
                ->index('fait_package_id');

            $table->string('item_type', 255);

            $table->unique(['package_id', 'item_type']);
        });
    }

    private function createItem(): void
    {
        if (Schema::hasTable('featured_items')) {
            return;
        }

        Schema::create('featured_items', function (Blueprint $table) {
            $table->id();

            DbTableHelper::morphUserColumn($table);

            DbTableHelper::morphItemColumn($table);

            $table->string('status', 30)
                ->index('fi_status');

            $table->bigInteger('package_id', false, true)
                ->nullable()
                ->index('fi_package_id');

            $table->string('package_duration_period', 30)
                ->nullable()
                ->index('fi_duration_period');

            $table->bigInteger('package_duration_value', false, true)
                ->nullable();

            $table->timestamp('expired_at')
                ->nullable();

            $table->timestamp('notified_at')
                ->nullable();

            $table->string('deleted_item_title', 255)
                ->nullable();

            $table->timestamps();
        });
    }

    private function createInvoice(): void
    {
        if (Schema::hasTable('featured_invoices')) {
            return;
        }

        Schema::create('featured_invoices', function (Blueprint $table) {
            $table->id();

            DbTableHelper::morphUserColumn($table);

            DbTableHelper::morphItemColumn($table);

            $table->bigInteger('package_id', false, true)
                ->nullable()
                ->index('fin_package_id');

            $table->bigInteger('featured_id', false, true)
                ->index('fin_featured_id')
                ->nullable();

            $table->string('status', 30)
                ->index('fin_status');

            $table->bigInteger('payment_gateway', false, true)
                ->index('fin_gateway')
                ->nullable();

            $table->decimal('price', 14, 2, true);

            $table->char('currency', 3);

            $table->string('deleted_item_title', 255)
                ->nullable();

            $table->timestamps();
        });
    }

    private function createTransaction(): void
    {
        if (Schema::hasTable('featured_transactions')) {
            return;
        }

        Schema::create('featured_transactions', function (Blueprint $table) {
            $table->id();

            DbTableHelper::morphUserColumn($table);

            DbTableHelper::morphItemColumn($table);

            $table->bigInteger('invoice_id', false, true)
                ->index('ft_invoice_id');

            $table->string('status', 30)
                ->index('ft_status');

            $table->bigInteger('payment_gateway', false, true)
                ->index('ft_gateway')
                ->nullable();

            $table->decimal('price', 14, 2, true);

            $table->char('currency', 3);

            $table->string('transaction_id', 255)
                ->nullable();

            $table->string('deleted_item_title', 255)
                ->nullable();

            $table->timestamps();
        });
    }

    private function createPackage(): void
    {
        if (Schema::hasTable('featured_packages')) {
            return;
        }

        Schema::create('featured_packages', function (Blueprint $table) {
            $table->id();

            $table->string('title', 255);

            $table->text('price')
                ->nullable();

            $table->string('duration_period', 30)
                ->nullable();

            $table->bigInteger('duration_value', false, true)
                ->nullable();

            $table->boolean('is_active')
                ->default(false);

            $table->boolean('is_free')
                ->default(false);

            $table->boolean('is_forever_duration')
                ->default(false);

            DbTableHelper::totalColumns($table, ['cancelled', 'active', 'end']);

            $table->string('applicable_role_type', 15)
                ->index();

            $table->string('applicable_item_type', 15)
                ->index();

            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('featured_items');
        Schema::dropIfExists('featured_invoices');
        Schema::dropIfExists('featured_transactions');
        Schema::dropIfExists('featured_packages');
        Schema::dropIfExists('featured_applicable_roles');
        Schema::dropIfExists('featured_applicable_item_types');
    }
};
