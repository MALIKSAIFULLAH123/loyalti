<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Profile\Jobs\EnforceCustomFieldUniqueNameJob;
use MetaFox\Profile\Jobs\EnforceCustomSectionUniqueNameJob;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Models\Section;

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
        // The threshold where we should push migration process to a queue
        // to not blocking installing/upgrading process
        $threshold = 1000;

        $this->enforceCustomField($threshold);
        $this->enforceCustomSection($threshold);
    }

    protected function enforceCustomField(int $threshold): void
    {
        if (!Schema::hasTable('user_custom_fields')) {
            return;
        }

        $total = Field::query()->count();

        if ($total >= $threshold) {
            EnforceCustomFieldUniqueNameJob::dispatch();

            return;
        }

        EnforceCustomFieldUniqueNameJob::dispatchSync();
    }

    protected function enforceCustomSection(int $threshold): void
    {
        if (!Schema::hasTable('user_custom_sections')) {
            return;
        }

        $total = Section::query()->count();

        if ($total >= $threshold) {
            EnforceCustomSectionUniqueNameJob::dispatch();

            return;
        }

        EnforceCustomSectionUniqueNameJob::dispatchSync();
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
