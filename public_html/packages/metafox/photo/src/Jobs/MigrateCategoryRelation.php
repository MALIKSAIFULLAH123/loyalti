<?php

namespace MetaFox\Photo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Photo\Repositories\Eloquent\CategoryRepository;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateCategoryRelation extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        resolve(CategoryRepository::class)->migrateCategoryRelationAfterImport('photo_category_relations');
    }
}
