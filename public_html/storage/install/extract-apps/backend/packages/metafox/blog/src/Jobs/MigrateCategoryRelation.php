<?php

namespace MetaFox\Blog\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Blog\Repositories\Eloquent\CategoryRepository;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateCategoryRelation extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        resolve(CategoryRepository::class)->migrateCategoryRelationAfterImport('blog_category_relations');
    }
}
