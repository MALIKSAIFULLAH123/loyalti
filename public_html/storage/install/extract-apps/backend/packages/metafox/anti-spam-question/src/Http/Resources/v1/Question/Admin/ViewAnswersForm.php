<?php

namespace MetaFox\AntiSpamQuestion\Http\Resources\v1\Question\Admin;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use MetaFox\AntiSpamQuestion\Models\Question as Model;
use MetaFox\AntiSpamQuestion\Repositories\QuestionAdminRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

/**
 * --------------------------------------------------------------------------
 *  Form Resource
 * --------------------------------------------------------------------------.
 *
 * This stub is used by MetaFox Generator.
 * Please complete it to treat as model resource.
 */

/**
 * @property ?Model $resource
 */
class ViewAnswersForm extends AbstractForm
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
        $answers = $this->resource->answers;
        $values  = [];
        foreach ($answers as $index => $answer) {
            $values['answers_' . $index] = $answer->answer;
        }

        $this->title(__p('antispamquestion::phrase.view_answers'))->setValue($values);
    }

    protected function initialize(): void
    {
        $answers = $this->resource->answers;
        $basic   = $this->addBasic()
            ->label($this->resource->toTitle());

        foreach ($answers as $index => $answer) {
            $basic->addFields(
                Builder::text('answers_' . $index)->readOnly()
            );
        }
    }
}
