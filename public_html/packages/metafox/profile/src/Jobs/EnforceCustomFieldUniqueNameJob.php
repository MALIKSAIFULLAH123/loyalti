<?php

namespace MetaFox\Profile\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Profile\Jobs\Traits\UniqueNameTrait;
use MetaFox\Profile\Models\Field;

class EnforceCustomFieldUniqueNameJob extends AbstractJob
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
        $query = Field::query();

        foreach ($query->cursor() as $field) {
            if (!$field instanceof Field) {
                continue;
            }

            $fieldName = $field->field_name ?: '';
            if (preg_match('%' . MetaFoxConstant::RESOURCE_IDENTIFIER_REGEX . '%', $fieldName)) {
                continue;
            }

            $labelValue       = $field->label;
            $descriptionValue = $field->description;

            $this->deleteOldPhrase($fieldName);

            $newName           = $this->parseUniqueCustomFieldName($fieldName);
            $field->field_name = uniqid(sprintf('custom_field_%s_%s_', $newName, $field->entityId()));

            $field->save();
            $field->refresh();

            $field->label       = $labelValue;
            $field->description = $descriptionValue;
        }
    }

    protected function deleteOldPhrase(string $fieldName): void
    {
        $labelKey       = 'profile::phrase.' . $fieldName . '_label';
        $descriptionKey = 'profile::phrase.' . $fieldName . '_description';

        $phraseRepository = resolve(PhraseRepositoryInterface::class);

        $phraseRepository->deletePhraseByKey($labelKey);
        $phraseRepository->deletePhraseByKey($descriptionKey);
    }
}
