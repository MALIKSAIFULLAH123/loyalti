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
        $this->createSevents();
        $this->createSeventPhotos();
        $this->createCategoryRelation();
        $this->createSeventsFavourite();
        $this->createAttend();
        $this->createTickets();
        $this->createUserTickets();
        $this->createInvoices();
        
        DbTableHelper::categoryTable('sevent_categories', true);
        DbTableHelper::categoryDataTable('sevent_category_data');
        DbTableHelper::textTable('sevent_text');
        DbTableHelper::streamTables('sevent');
        DbTableHelper::createTagDataTable('sevent_tag_data');
        
        if (!Schema::hasColumn('sevent_categories', 'level')) {
            Schema::table('sevent_categories', function (Blueprint $table) {
                $table->unsignedInteger('level')
                    ->default(1);
            });
        }
        
        if (!Schema::hasColumn('sevent_categories', 'deleted_at')) {
            Schema::table('sevent_categories', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    protected function createSeventPhotos()
    {
        if (!Schema::hasTable('sevent_images')) {
            Schema::create('sevent_images', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sevent_id');
                DbTableHelper::imageColumns($table);
                $table->unsignedInteger('ordering')->default(1);
            });
        }
    }
    
    protected function createCategoryRelation()
    {
        $tableName = 'sevent_category_relations';
        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) {
            $table->unsignedInteger('parent_id')->index();
            $table->unsignedInteger('child_id')->index();
            $table->unsignedInteger('depth')->index();
        });
    }

    protected function createSevents()
    {
        if (Schema::hasTable('sevents')) {
            return;
        }
    
        Schema::create('sevents', function (Blueprint $table) {
            DbTableHelper::morphOwnerColumn($table);
            DbTableHelper::morphUserColumn($table);
            DbTableHelper::approvedColumn($table);
            DbTableHelper::featuredColumn($table);
            DbTableHelper::privacyColumn($table);
            DbTableHelper::imageColumns($table);
            DbTableHelper::sponsorColumn($table);
            
            $table->id();
            $table->string('title', 255);    
            $table->longText('short_description')->nullable();
            $table->longText('terms')->nullable();
            $table->string('tags', 255)->nullable();
            $table->string('online_link', 255)->nullable();
            $table->string('video', 255)->nullable();
            $table->string('location_name', 255)->nullable();
            $table->decimal('location_latitude', 30, 8)->nullable();
            $table->decimal('location_longitude', 30, 8)->nullable();
            $table->string('country_iso', 2)->nullable();
            $table->string('module_id', 255);
            $table->tinyInteger('is_draft')
                    ->default(0);
            $table->tinyInteger('is_online')
                    ->default(0);
            $table->timestamp('start_date')
                ->nullable();
            $table->timestamp('end_date')
                    ->nullable();

            $table->tinyInteger('is_host')
                    ->default(0)->nullable();
                    
            $table->bigInteger('host_image_file_id')->nullable();
            $table->string('host_title', 255)->nullable();
            $table->string('host_contact', 255)->nullable();
            $table->string('host_website', 255)->nullable();
            $table->string('host_facebook', 255)->nullable();
            $table->text('host_description', 255)->nullable();

            DbTableHelper::totalColumns($table, 
                ['view', 'like', 'dislike', 'comment', 'attachment', 'attending', 'interested', 
                    'reply', 'share',  'tag']
            );
            $table->timestamps();
        });
    }

    protected function createInvoices()
    {
        if (!Schema::hasTable('sevent_invoices')) {
            Schema::create('sevent_invoices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sevent_id');
                DbTableHelper::imageColumns($table);
                DbTableHelper::morphUserColumn($table);
                $table->decimal('price', 14, 2)
                    ->default(0.0);
                $table->char('currency_id', 3);
                $table->integer('payment_gateway', false, true)
                    ->default(0);
                $table->integer('ticket_id', false, true)
                    ->default(0);
                $table->integer('qty', false, true)
                    ->default(0);
                $table->string('status', 15)
                    ->index('sevent_invoice_status');
                $table->timestamp('paid_at')
                    ->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sevent_invoice_transactions')) {
            Schema::create('sevent_invoice_transactions', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('invoice_id', false, true);
                $table->string('status', 15)
                    ->index('sevent_transaction_status');
                $table->decimal('price', 14, 2, true);
                $table->char('currency_id', 3);
                $table->text('transaction_id')
                    ->nullable();
                $table->integer('payment_gateway', false, true)
                    ->default(0);
                $table->timestamps();
            });
        }
    }

    protected function createTickets()
    {
        if (Schema::hasTable('sevent_tickets')) {
            return;
        }
    
        Schema::create('sevent_tickets', function (Blueprint $table) {
            DbTableHelper::morphOwnerColumn($table);
            DbTableHelper::morphUserColumn($table);
            DbTableHelper::imageColumns($table);

            $table->id();
            $table->string('title', 255);    
            $table->longText('description');
            $table->decimal('amount', 14, 2)->default(0.0);
            $table->mediumInteger('qty')
                    ->default(0)->nullable();
            $table->mediumInteger('temp_qty')
                ->default(0)->nullable();
            $table->mediumInteger('sevent_id')
                    ->default(0)->nullable();
            $table->tinyInteger('is_unlimited')
                    ->default(0)->nullable();
            $table->timestamp('expiry_date')
                    ->nullable();
            DbTableHelper::totalColumns($table, 
                ['view', 'like', 'dislike', 'comment', 'sales', 'reply', 'share',  'tag']
            );
            $table->timestamps();
        });
    }

    protected function createUserTickets()
    {
        if (Schema::hasTable('sevent_user_tickets')) {
            return;
        }
    
        Schema::create('sevent_user_tickets', function (Blueprint $table) {
            DbTableHelper::morphOwnerColumn($table);
            DbTableHelper::morphUserColumn($table);
            DbTableHelper::imageColumns($table);

            $table->id();
            $table->mediumInteger('sevent_id')
                ->default(0)->nullable(); 
            $table->mediumInteger('pdf_file_id')
                ->default(0)->nullable(); 
            $table->mediumInteger('ticket_id')
                ->default(0)->nullable(); 
            $table->string('number', 255)->nullable(); 
            $table->timestamp('paid_at')
                    ->nullable();
            $table->timestamps();
        });
    }

    protected function createAttend()
    {
        if (Schema::hasTable('sevent_attends')) {
            return;
        }
        
        Schema::create('sevent_attends', function (Blueprint $table) {
            $table->id();
            $table->mediumInteger('user_id')
                ->default(0)->nullable(); 
            $table->mediumInteger('sevent_id')
                ->default(0)->nullable(); 
            $table->mediumInteger('type_id')
                ->default(0)->nullable(); 
            $table->timestamps();
        });
    }

    protected function createSeventsFavourite()
    {
        if (Schema::hasTable('sevent_favourite')) {
            return;
        }

        Schema::create('sevent_favourite', function (Blueprint $table) {
            DbTableHelper::morphOwnerColumn($table);
            $table->id();
            $table->integer('sevent_id');
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
        DbTableHelper::dropStreamTables('sevents');
        Schema::dropIfExists('sevents');
        Schema::dropIfExists('sevent_tickets');
        Schema::dropIfExists('sevent_images');
        Schema::dropIfExists('sevent_invoices');
        Schema::dropIfExists('sevent_invoice_transactions');
        Schema::dropIfExists('sevent_text');
        Schema::dropIfExists('sevent_favourite');
        Schema::dropIfExists('sevent_categories');
        Schema::dropIfExists('sevent_category_data');
        Schema::dropIfExists('sevent_category_relations');
        Schema::dropIfExists('sevent_tag_data');
    }
};
