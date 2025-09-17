<?php

namespace MetaFox\Profile\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Profile\Support\CustomField;

/**
 * stub: packages/jobs/job-queued.stub.
 */
class MigrateUserCustomValueJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $customValue = \MetaFox\Profile\Models\Field::query()
            ->leftJoin('user_custom_value', function (JoinClause $joinClause) {
                $joinClause->on('user_custom_value.field_id', '=', 'user_custom_fields.id');
            })->where('user_custom_fields.edit_type', CustomField::MULTI_CHOICE)
            ->select('user_custom_value.*')->get();

        $insertData = [];
        if (empty($customValue)) {
            return;
        }

        foreach ($customValue as $item) {
            $values = json_decode($item->field_value_text);
            if (empty($values)) {
                continue;
            }

            foreach ($values as $value) {
                $insertData[] = ['item_id' => $item->id, 'custom_option_id' => $value];
            }
        }

        $insertData = collect($insertData)->chunk(100)->toArray();
        foreach ($insertData as $data) {
            if (empty($data)) {
                continue;
            }

            \MetaFox\Profile\Models\OptionData::query()->insert($data);
        }
    }
}
