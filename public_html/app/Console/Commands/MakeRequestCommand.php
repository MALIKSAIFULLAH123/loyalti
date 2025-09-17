<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MetaFox\Platform\Console\CodeGeneratorTrait;

/**
 * Class MakeRequestCommand.
 *
 * @link    \MetaFox\Core\Http\Controllers\Api\v1\CodeAdminController::makeFormRequest()
 * @link    \MetaFox\Core\Http\Requests\v1\Code\Admin\MakeRequestRequest::rules()
 */
class MakeRequestCommand extends Command
{
    use CodeGeneratorTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:make-request {package}
    {--name= : }
    {--action= : Request name}
    {--overwrite : Overwrite existing files?}
    {--ver=v1 : Version , etc: v1}
    {--dry : Dry run}
    {--test=v1 : Version , etc: v1}
    {--admin= :  Generate for admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate http form requests.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->generateFormRequest(
            $this->option('admin'),
            $this->option('name'),
            $this->option('action'),
            $this->option('ver')
        );

        return 0;
    }
}
