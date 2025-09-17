<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use MetaFox\Core\Constants;
use MetaFox\User\Models\User;
use MetaFox\User\Repositories\Eloquent\UserRepository;
use Symfony\Component\Console\Input\InputOption;

class StatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'metafox:stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Content statistic';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function handle()
    {

        $types = app('core.drivers')->where(['type' => Constants::DRIVER_TYPE_ENTITY_CONTENT])
            ->pluck('name')
            ->toArray();

        $total = 0;
        foreach ($types as $type) {
            $value = $this->count($type);
            $total += $value;
            $this->components->twoColumnDetail($type, number_format($value));
        }

        $this->components->twoColumnDetail('Total', number_format($total));

        if ($this->option('csv')) {
            $userRepository = resolve(UserRepository::class);
            $users = $userRepository->getModel()->newQuery()->limit(10)
                ->where('id', '>', 1)
                ->get()
                ->map(function (User $user) {
                    return implode(',', [$user->id, $user->email, '123456']);
                })->toArray();

            echo implode(PHP_EOL, $users);
        }


        return Command::SUCCESS;
    }


    public function count(string $type)
    {
        $modelName = Relation::getMorphedModel($type);

        /** @var Model $table */
        $table = resolve($modelName);
        return $table->count();
    }

    protected function getOptions()
    {
        return [
            ['csv', null, InputOption::VALUE_NONE, "Get CSV user for load test"]
        ];
    }
}