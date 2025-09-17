<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Advertise\Support\Support;

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
        $this->createAdvertises();
        $this->createPlacements();
        $this->createPlacementDescription();
        $this->createSponsors();
        $this->createAdvertiseHides();
        $this->createInvoices();
        $this->createAdvertiseCountries();
        $this->createAdvertiseLanguages();
        $this->createAdvertiseGenders();
        $this->createAdvertiseStatistics();
        $this->createInvoiceTransactions();
        $this->createReports();
    }

    protected function createPlacementDescription(): void
    {
        if (Schema::hasTable('advertise_placement_text')) {
            return;
        }

        DbTableHelper::textTable('advertise_placement_text');
    }

    protected function createReports(): void
    {
        if (Schema::hasTable('advertise_reports')) {
            return;
        }

        Schema::create('advertise_reports', function (Blueprint $table) {
            $table->id();
            DbTableHelper::morphItemColumn($table);
            $table->bigInteger('total_impression', false, true)
                ->default(0);
            $table->bigInteger('total_click', false, true)
                ->default(0);
            $table->string('date_type', 10);
            $table->timestamp('date_value');
        });
    }

    protected function createInvoiceTransactions(): void
    {
        if (Schema::hasTable('advertise_invoice_transactions')) {
            return;
        }

        Schema::create('advertise_invoice_transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('invoice_id', false, true);
            $table->string('status', 15)
                ->index('advertise_transaction_status');
            $table->decimal('price', 14, 2, true);
            $table->char('currency_id', 3);
            $table->text('transaction_id')
                ->nullable();
            $table->timestamps();
        });
    }

    protected function createAdvertiseStatistics(): void
    {
        if (Schema::hasTable('advertise_statistics')) {
            return;
        }

        Schema::create('advertise_statistics', function (Blueprint $table) {
            $table->id();
            DbTableHelper::morphItemColumn($table);
            $table->bigInteger('total_impression', false, true)
                ->default(0);
            $table->bigInteger('total_click', false, true)
                ->default(0);
        });
    }

    protected function createAdvertiseGenders(): void
    {
        if (Schema::hasTable('advertise_genders')) {
            return;
        }

        Schema::create('advertise_genders', function (Blueprint $table) {
            $table->id();
            DbTableHelper::morphItemColumn($table);
            $table->integer('gender_id');
        });
    }

    protected function createAdvertiseLanguages(): void
    {
        if (Schema::hasTable('advertise_languages')) {
            return;
        }

        Schema::create('advertise_languages', function (Blueprint $table) {
            $table->id();
            DbTableHelper::morphItemColumn($table);
            $table->string('language_code', 15);
        });
    }

    protected function createAdvertiseCountries(): void
    {
        if (Schema::hasTable('advertise_countries')) {
            return;
        }

        Schema::create('advertise_countries', function (Blueprint $table) {
            $table->id();
            DbTableHelper::morphItemColumn($table);
            $table->string('address', 255)
                ->nullable();
            $table->string('city_code', 15)
                ->nullable();
            $table->string('state_code', 15)
                ->nullable();
            $table->decimal('latitude', 30, 8)->nullable();
            $table->decimal('longitude', 30, 8)->nullable();
            $table->char('country_code', 2);
        });
    }

    protected function createInvoices(): void
    {
        if (Schema::hasTable('advertise_invoices')) {
            return;
        }

        Schema::create('advertise_invoices', function (Blueprint $table) {
            $table->id();
            DbTableHelper::morphItemColumn($table);
            DbTableHelper::morphUserColumn($table);
            $table->char('currency_id', 3)
                ->nullable();
            $table->decimal('price', 14, 2, true)
                ->default('0.00');
            $table->integer('payment_gateway')
                ->default(0);
            $table->string('payment_status')
                ->nullable();
            $table->timestamp('paid_at')
                ->nullable();
            $table->timestamps();
        });
    }

    protected function createAdvertiseHides()
    {
        if (Schema::hasTable('advertise_hides')) {
            return;
        }

        Schema::create('advertise_hides', function (Blueprint $table) {
            $table->id();
            DbTableHelper::morphItemColumn($table);
            DbTableHelper::morphUserColumn($table);
            $table->timestamps();
        });
    }

    protected function createSponsors(): void
    {
        if (Schema::hasTable('advertise_sponsors')) {
            return;
        }

        Schema::create('advertise_sponsors', function (Blueprint $table) {
            $table->id();
            DbTableHelper::morphItemColumn($table);
            DbTableHelper::morphUserColumn($table);
            $table->string('title', 255);
            $table->string('status', 20)
                ->default(Support::ADVERTISE_STATUS_UNPAID)
                ->index('advertise_sponsor_status');
            DbTableHelper::activeColumn($table);
            $table->timestamp('start_date')
                ->nullable();
            $table->timestamp('end_date')
                ->nullable();
            $table->integer('total_impression', false, true)
                ->default(0);
            $table->integer('total_click', false, true)
                ->default(0);
            $table->integer('age_from', false, true)
                ->nullable()
                ->default(null);
            $table->integer('age_to')
                ->nullable()
                ->default(null);
            $table->timestamp('completed_at')
                ->nullable();
            $table->timestamps();
        });
    }

    protected function createPlacements(): void
    {
        if (Schema::hasTable('advertise_placements')) {
            return;
        }

        Schema::create('advertise_placements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('price');
            $table->text('allowed_user_roles')
                ->nullable();
            DbTableHelper::activeColumn($table);
            /*
             * cpm & ppc
             */
            $table->string('placement_type', 10);
            $table->bigInteger('ordering', false, true)
                ->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    protected function createAdvertises(): void
    {
        if (Schema::hasTable('advertises')) {
            return;
        }

        Schema::create('advertises', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('placement_id', false, true);
            DbTableHelper::morphUserColumn($table);
            $table->string('title', 255);
            /*
             * image & html
             */
            $table->string('creation_type', 10)
                ->index('advertises_creation_type');
            $table->string('status', 20)
                ->default(Support::ADVERTISE_STATUS_UNPAID)
                ->index('advertises_status');
            DbTableHelper::activeColumn($table);
            $table->string('url', 255)
                ->nullable();
            $table->timestamp('start_date')
                ->nullable();
            $table->timestamp('end_date')
                ->nullable();
            $table->integer('total_impression', false, true)
                ->default(0);
            $table->integer('total_click', false, true)
                ->default(0);
            /*
             * cpm & ppc
             */
            $table->string('advertise_type', 10)
                ->index('advertise_type');
            $table->integer('age_from', false, true)
                ->nullable()
                ->default(null);
            $table->integer('age_to')
                ->nullable()
                ->default(null);
            $table->bigInteger('advertise_file_id', false, true)
                ->default(0);
            /*
             * tooltip
             */
            $table->text('image_values')
                ->nullable();
            /*
             * title, description
             */
            $table->text('html_values')
                ->nullable();
            $table->timestamp('completed_at')
                ->nullable();
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
        Schema::dropIfExists('advertises');
        Schema::dropIfExists('advertise_placements');
        Schema::dropIfExists('advertise_sponsors');
        Schema::dropIfExists('advertise_hides');
        Schema::dropIfExists('advertise_invoices');
        Schema::dropIfExists('advertise_invoice_transactions');
        Schema::dropIfExists('advertise_countries');
        Schema::dropIfExists('advertise_languages');
        Schema::dropIfExists('advertise_genders');
        Schema::dropIfExists('advertise_statistics');
        Schema::dropIfExists('advertise_reports');
        Schema::dropIfExists('advertise_placement_text');
    }
};
