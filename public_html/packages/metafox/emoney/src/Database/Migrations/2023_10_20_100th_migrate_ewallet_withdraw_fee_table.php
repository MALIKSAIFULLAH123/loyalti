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
        $this->handleWithdrawRequestTable();
        $this->handleTransactionTable();
        $this->handleStatisticTable();
    }

    private function handleWithdrawRequestTable(): void
    {
        if (!Schema::hasTable('emoney_withdraw_requests')) {
            return;
        }

        if (!Schema::hasColumns('emoney_withdraw_requests', ['total', 'fee'])) {
            Schema::table('emoney_withdraw_requests', function (Blueprint $table) {
                $table->decimal('total', 14, 2)
                    ->default(0);
                $table->decimal('fee', 14, 2)
                    ->default(0);
            });

            \MetaFox\EMoney\Models\WithdrawRequest::query()
                ->get()
                ->each(function ($request) {
                    $request->update(['total' => $request->amount]);
                });
        }
    }

    private function handleStatisticTable(): void
    {
        if (!Schema::hasTable('emoney_statistics')) {
            return;
        }

        if (!Schema::hasColumns('emoney_statistics', ['total_purchased'])) {
            Schema::table('emoney_statistics', function (Blueprint $table) {
                $table->decimal('total_purchased', 14, 2, true)
                    ->default(0);
            });
        }
    }

    private function handleTransactionTable(): void
    {
        if (!Schema::hasTable('emoney_transactions')) {
            return;
        }

        if (!Schema::hasColumns('emoney_transactions', ['source', 'outgoing_order_id', 'extra'])) {
            Schema::table('emoney_transactions', function (Blueprint $table) {
                $table->string('source', 15)
                    ->default(\MetaFox\EMoney\Support\Support::TRANSACTION_SOURCE_INCOMING);
                $table->string('outgoing_order_id', 255)
                    ->nullable()
                    ->default(null);
                $table->text('extra')
                    ->nullable()
                    ->default(null);
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
    }
};
