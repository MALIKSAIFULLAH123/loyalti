<?php

namespace MetaFox\Profile\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Profile\Jobs\Traits\UniqueNameTrait;
use MetaFox\Profile\Models\Section;

class EnforceCustomSectionUniqueNameJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use UniqueNameTrait;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $query = Section::query();

        foreach ($query->cursor() as $section) {
            if (!$section instanceof Section) {
                continue;
            }

            $sectionName = $section->name ?: '';
            if (preg_match('%' . MetaFoxConstant::RESOURCE_IDENTIFIER_REGEX . '%', $sectionName)) {
                continue;
            }

            $newName = $this->parseUniqueCustomFieldName($sectionName);

            $newName = empty($newName) ? 'custom_section' : $newName;

            $section->update(['name' => uniqid(sprintf('%s_%s_', $newName, $section->entityId()))]);
        }
    }
}
