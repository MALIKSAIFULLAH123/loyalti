<?php

namespace MetaFox\AntiSpamQuestion\Http\Resources\v1\Question\Admin;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use MetaFox\AntiSpamQuestion\Models\Answer;
use MetaFox\AntiSpamQuestion\Models\Question as Model;
use MetaFox\AntiSpamQuestion\Repositories\QuestionAdminRepositoryInterface;
use MetaFox\Core\Support\Facades\Language;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class UpdateQuestionForm
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateQuestionForm extends StoreQuestionForm
{
    public function boot(QuestionAdminRepositoryInterface $repository, ?int $id = null)
    {
        if ($id === null) {
            throw new ModelNotFoundException();
        }

        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->action('admincp/antispamquestion/question/' . $this->resource->entityId())
            ->asPut()
            ->setValue([
                'question'          => Language::getPhraseValues($this->resource->question),
                'is_active'         => $this->resource->is_active,
                'is_case_sensitive' => $this->resource->is_case_sensitive,
                'answers'           => $this->getAnswersForEdit(),
                'image'             => $this->resource->image,
            ]);
    }

    protected function getAnswersForEdit(): array
    {
        return $this->resource->answers->map(function (Answer $answer) {
            return [
                'id'       => $answer->id,
                'value'    => $answer->answer,
                'ordering' => $answer->ordering,
            ];
        })->toArray();
    }
}
