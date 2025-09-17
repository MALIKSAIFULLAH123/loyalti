<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Facades\PolicyGate;

class PolicyCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'policy:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache policy data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $file1 = base_path('bootstrap/cache/policy.php');
        $file2 = base_path('bootstrap/cache/policy_rules.php');
        try{
            $repository = resolve(DriverRepositoryInterface::class);

            $policies = $repository->loadPolicies();
            $policyRules = $repository->loadPolicyRules();

            export_to_file($file1, $policies);
            export_to_file($file2, $policyRules);

        }catch (\Exception $exception){
            //
        }

        return Command::SUCCESS;
    }
}
