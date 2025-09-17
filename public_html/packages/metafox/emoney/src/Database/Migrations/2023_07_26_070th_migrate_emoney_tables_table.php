<?php

use MetaFox\EMoney\Support\Support;
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
        $this->createTransaction();
        $this->createStatistic();
        $this->createCurrencyConversionRate();
        $this->createCurrencyConversionRateLog();
        $this->createCurrencyConverter();
        $this->createWithdrawMethod();
        $this->createWithdrawRequest();
        $this->createWithdrawRequestReason();
    }

    protected function createCurrencyConversionRate(): void
    {
        if (Schema::hasTable('emoney_currency_conversion_rates')) {
            return;
        }

        Schema::create('emoney_currency_conversion_rates', function (Blueprint $table) {
            $table->id();
            $table->char('base', 3);
            $table->char('target', 3);
            $table->string('type', 15)
                ->nullable()
                ->default(null);
            $table->decimal('exchange_rate', 20, Support::MAXIMUM_EXCHANGE_RATE_DECIMAL_PLACE_NUMBER)
                ->nullable()
                ->default(null);
            $table->bigInteger('log_id')
                ->nullable()
                ->default(null)
                ->index('eccr_log_id');
            $table->timestamps();
            $table->unique(['base', 'target'], 'eccr_base_target');
        });
    }

    protected function createCurrencyConversionRateLog(): void
    {
        if (Schema::hasTable('emoney_currency_conversion_rate_logs')) {
            return;
        }

        Schema::create('emoney_currency_conversion_rate_logs', function (Blueprint $table) {
            $table->id();
            $table->string('service', 30)
                ->index('eccrl_service');
            $table->char('from', 3);
            $table->char('to', 3);
            $table->decimal('exchange_rate', 20, Support::MAXIMUM_EXCHANGE_RATE_DECIMAL_PLACE_NUMBER);
            $table->text('payload')
                ->nullable()
                ->default(null);
            $table->text('response')
                ->nullable()
                ->default(null);
            $table->timestamps();
        });
    }

    protected function createWithdrawRequestReason(): void
    {
        if (Schema::hasTable('emoney_withdraw_request_reasons')) {
            return;
        }

        Schema::create('emoney_withdraw_request_reasons', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('request_id', false, true)
                ->index('ewrr_request_id');
            $table->string('type', 30);
            $table->text('message');
            $table->timestamps();
        });
    }

    protected function createCurrencyConverter(): void
    {
        if (Schema::hasTable('emoney_currency_converters')) {
            return;
        }

        Schema::create('emoney_currency_converters', function (Blueprint $table) {
            $table->id();
            $table->string('service', 30)
                ->unique('ecc_service');
            $table->string('service_class', 255);
            $table->string('title', 255);
            $table->text('description')
                ->nullable()
                ->default(null);
            $table->text('config')
                ->default(null)
                ->nullable();
            $table->string('link')
                ->nullable()
                ->default(null);
            $table->boolean('is_default')
                ->default(false);
            $table->timestamps();
        });
    }

    protected function createPriceColumns(Blueprint $table, string $prefix): void
    {
        $table->char(sprintf('%s_currency', $prefix), 3);
        $table->decimal(sprintf('%s_price', $prefix), 14, 2);
    }

    protected function createTransaction(): void
    {
        if (Schema::hasTable('emoney_transactions')) {
            return;
        }

        Schema::create('emoney_transactions', function (Blueprint $table) {
            $table->id();
            DbTableHelper::morphUserColumn($table);
            DbTableHelper::morphOwnerColumn($table);
            DbTableHelper::morphItemColumn($table);
            $table->string('type', 30)
                ->default(Support::INCOMING_TRANSACTION_TYPE_RECEIVED);
            $table->string('module_id', 255)
                ->nullable()
                ->default(null);
            $this->createPriceColumns($table, 'total');
            $this->createPriceColumns($table, 'commission');
            $this->createPriceColumns($table, 'actual');
            $this->createPriceColumns($table, 'balance');
            $table->decimal('current_balance_price', 14, 2)
                ->nullable()
                ->default(null);
            $table->decimal('exchange_rate', 20, Support::MAXIMUM_EXCHANGE_RATE_DECIMAL_PLACE_NUMBER);
            $table->bigInteger('exchange_rate_id', false, true)
                ->nullable()
                ->default(null);
            $table->bigInteger('exchange_rate_log_id', false, true)
                ->nullable()
                ->default(null);
            $table->string('status', 30)
                ->index('et_status');
            $table->timestamp('available_at')
                ->nullable()
                ->default(null);
            $table->timestamps();
        });
    }

    protected function createWithdrawRequest(): void
    {
        if (Schema::hasTable('emoney_withdraw_requests')) {
            return;
        }

        Schema::create('emoney_withdraw_requests', function (Blueprint $table) {
            $table->id();
            DbTableHelper::morphUserColumn($table);
            $table->char('currency', 3);
            $table->decimal('amount', 14, 2);
            $table->string('withdraw_service', 30)
                ->index('ewr_withdraw_service');
            $table->string('status', 30)
                ->index('ewr_status');
            $table->timestamp('processed_at')
                ->nullable()
                ->default(null);
            $table->string('transaction_id', 255)
                ->nullable()
                ->default(null);
            $table->timestamps();
        });
    }

    protected function createWithdrawMethod(): void
    {
        if (Schema::hasTable('emoney_withdraw_methods')) {
            return;
        }

        Schema::create('emoney_withdraw_methods', function (Blueprint $table) {
            $table->integer('id', true, true);
            $table->string('title', 255);
            $table->text('description')
                ->nullable()
                ->default(null);
            $table->string('service', 30)
                ->unique('ewm_service');
            $table->string('service_class', 255);
            $table->string('module_id', 100);
            $table->boolean('is_active')
                ->default(false);
        });
    }

    protected function createStatistic(): void
    {
        if (Schema::hasTable('emoney_statistics')) {
            return;
        }

        Schema::create('emoney_statistics', function (Blueprint $table) {
            $table->id();
            DbTableHelper::morphUserColumn($table);
            $table->char('currency', 3)
                ->default('USD');
            $table->decimal('total_pending_transaction', 14, 2)->default(0);
            $table->decimal('total_balance', 14, 2)->default(0);
            $table->decimal('total_pending', 14, 2)->default(0);
            $table->decimal('total_earned', 14, 2)->default(0);
            $table->decimal('total_withdrawn', 14, 2)->default(0);
            $table->unique(['user_id', 'user_type', 'currency'], 'es_user_currency');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('emoney_currency_conversion_rates');
        Schema::dropIfExists('emoney_currency_conversion_rate_logs');
        Schema::dropIfExists('emoney_statistics');
        Schema::dropIfExists('emoney_withdraw_methods');
        Schema::dropIfExists('emoney_withdraw_requests');
        Schema::dropIfExists('emoney_transactions');
        Schema::dropIfExists('emoney_currency_converters');
        Schema::dropIfExists('emoney_withdraw_request_reasons');
    }
};
