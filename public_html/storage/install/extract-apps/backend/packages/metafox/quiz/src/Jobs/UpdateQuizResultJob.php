<?php

namespace MetaFox\Quiz\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Repositories\QuizRepositoryInterface;

class UpdateQuizResultJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected Quiz $quiz)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        resolve(QuizRepositoryInterface::class)->calculateQuizResults($this->quiz);
    }
}
