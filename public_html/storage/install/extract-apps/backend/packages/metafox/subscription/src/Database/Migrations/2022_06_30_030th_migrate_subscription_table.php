<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;
use MetaFox\Subscription\Support\Helper;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models\
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('subscription_packages')) {
            Schema::create('subscription_packages', function (Blueprint $table) {
                $table->id();
                $this->addTitleColumn($table);
                $this->addStatusColumn($table);
                $table->text('price')
                    ->nullable();
                $table->text('recurring_price')
                    ->nullable();
                $table->enum('recurring_period', Helper::getRecurringPeriodType())
                    ->nullable();
                $table->integer('upgraded_role_id', false, true);
                $table->string('image_path', 255)
                    ->nullable();
                $table->string('image_server_id', 32)->default('public');
                $table->boolean('is_on_registration')
                    ->default(false)
                    ->index();
                $table->boolean('is_popular')
                    ->default(false);
                $table->boolean('is_free')
                    ->default(false);
                DbTableHelper::totalColumns($table, ['success', 'pending', 'canceled', 'expired']);
                $table->text('visible_roles')
                    ->nullable();
                $table->text('allowed_renew_type')
                    ->nullable();
                $table->integer('days_notification_before_subscription_expired', false, true)
                    ->default(0);
                $table->string('background_color_for_comparison', 50)
                    ->nullable();
                $this->addOrderingColumn($table);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('subscription_dependency_packages')) {
            Schema::create('subscription_dependency_packages', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('current_package_id', false, true);
                $table->bigInteger('dependency_package_id', false, true);
                $table->enum('dependency_type', Helper::getDependencyTypes());
                $table->unique(['current_package_id', 'dependency_package_id', 'dependency_type'], 'unique_dependency_relation');
            });
        }

        if (!Schema::hasTable('subscription_cancel_reasons')) {
            Schema::create('subscription_cancel_reasons', function (Blueprint $table) {
                $table->id();
                $this->addTitleColumn($table);
                $this->addStatusColumn($table);
                $table->boolean('is_default')
                    ->default(false);
                DbTableHelper::totalColumns($table, ['canceled']);
                $this->addOrderingColumn($table);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('subscription_invoices')) {
            Schema::create('subscription_invoices', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('package_id', false, true);
                DbTableHelper::morphUserColumn($table);
                $this->addCurrencyColumn($table);
                $this->addPriceColumn($table, 'initial_price');
                $this->addPriceColumn($table, 'recurring_price', true);
                $this->addStatusColumn($table, 'payment_status', Helper::getPaymentStatus(), true);
                $table->enum('renew_type', Helper::getRenewType())
                    ->nullable()
                    ->index();
                $this->addPaymentGatewayColumn($table);
                $table->boolean('is_canceled_by_gateway')
                    ->default(false);
                $table->timestamp('created_at')
                    ->nullable();
                $table->timestamp('activated_at')
                    ->nullable();
                $table->timestamp('expired_at')
                    ->nullable();
                $table->timestamp('notified_at')
                    ->nullable();
            });
        }

        if (!Schema::hasTable('subscription_user_cancel_reasons')) {
            Schema::create('subscription_user_cancel_reasons', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('invoice_id', false, true);
                $table->bigInteger('reason_id', false, true);
                $table->timestamp('created_at')
                    ->nullable();
            });
        }

        if (!Schema::hasTable('subscription_comparisons')) {
            Schema::create('subscription_comparisons', function (Blueprint $table) {
                $table->id();
                $this->addTitleColumn($table);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('subscription_comparisons_data')) {
            Schema::create('subscription_comparisons_data', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('comparison_id', false, true);
                $table->bigInteger('package_id', false, true);
                $table->enum('type', Helper::getComparisonTypes());
                $table->text('value')
                    ->nullable();
                $table->timestamps();
                $table->unique(['comparison_id', 'package_id'], 'comparison_package');
            });
        }

        if (!Schema::hasTable('subscription_invoice_transactions')) {
            Schema::create('subscription_invoice_transactions', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('invoice_id', false, true)
                    ->index();
                $this->addStatusColumn($table, 'payment_status', Helper::getPaymentStatus(), true);
                $this->addCurrencyColumn($table);
                $table->enum('payment_type', Helper::getPaymentType());
                $this->addPaymentGatewayColumn($table);
                $table->mediumText('transaction_id')
                    ->nullable(true);
                $this->addPriceColumn($table, 'paid_price', true);
                $table->timestamp('created_at')
                    ->nullable();
            });
        }

        if (!Schema::hasTable('subscription_packages_text')) {
            DbTableHelper::textTable('subscription_packages_text');
        }

        if (!Schema::hasTable('subscription_pending_registration_users')) {
            Schema::create('subscription_pending_registration_users', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('invoice_id', false, true)
                    ->index();
                DbTableHelper::morphUserColumn($table);
                $table->timestamp('created_at');
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
        Schema::dropIfExists('subscription_packages');
        Schema::dropIfExists('subscription_cancel_reasons');
        Schema::dropIfExists('subscription_invoices');
        Schema::dropIfExists('subscription_user_cancel_reasons');
        Schema::dropIfExists('subscription_invoice_transactions');
        Schema::dropIfExists('subscription_comparisons');
        Schema::dropIfExists('subscription_pending_registration_users');
        Schema::dropIfExists('subscription_dependency_packages');
        Schema::dropIfExists('subscription_packages_text');
    }

    protected function addTitleColumn(Blueprint $table, string $name = 'title', int $length = 255): void
    {
        $table->string($name, $length);
    }

    protected function addStatusColumn(Blueprint $table, string $name = 'status', ?array $values = null, bool $nullable = false): void
    {
        if (null === $values) {
            $values = Helper::getItemStatus();
        }

        $table->enum($name, $values)
            ->nullable($nullable)
            ->index();
    }

    protected function addOrderingColumn(Blueprint $table, string $name = 'ordering'): void
    {
        $table->bigInteger($name)
            ->index();
    }

    protected function addCurrencyColumn(Blueprint $table, string $name = 'currency'): void
    {
        $table->char($name, 3);
    }

    protected function addPaymentGatewayColumn(Blueprint $table, string $name = 'payment_gateway'): void
    {
        $table->integer($name, false, true)
            ->default(0);
    }

    protected function addPriceColumn(Blueprint $table, string $name, bool $nullable = false): void
    {
        if ($nullable) {
            $table->decimal($name, 14, 2, true)
                ->nullable($nullable);

            return;
        }

        $table->decimal($name, 14, 2, true)
            ->default('0.00');
    }
};
