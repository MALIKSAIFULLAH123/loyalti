<?php

namespace MetaFox\Page\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Page\Repositories\Eloquent\PageCategoryRepository;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateCategoryRelation extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        resolve(PageCategoryRepository::class)->migrateCategoryRelationAfterImport('page_category_relations');
    }
}
