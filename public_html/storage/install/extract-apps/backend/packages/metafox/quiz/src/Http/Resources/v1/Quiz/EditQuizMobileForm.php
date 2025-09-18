<?php

namespace MetaFox\Quiz\Http\Resources\v1\Quiz;

use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Quiz\Http\Requests\v1\Quiz\CreateFormRequest;
use MetaFox\Quiz\Models\Answer;
use MetaFox\Quiz\Models\Question;
use MetaFox\Quiz\Models\Quiz as Model;
use MetaFox\Quiz\Policies\QuizPolicy;
use MetaFox\Quiz\Repositories\QuizRepositoryInterface;

/**
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditQuizMobileForm extends CreateQuizMobileForm
{
    public function boot(CreateFormRequest $request, QuizRepositoryInterface $repository, ?int $id = null): void
    {
        $context        = user();
        $this->resource = $repository->find($id);
        $this->setOwner($this->resource->owner);
        policy_authorize(QuizPolicy::class, 'update', $context, $this->resource);
    }

    protected function prepare(): void
    {
        $questions = $this->resource->questions->map(function (Question $question) {
            return [
                'question' => $question->question,
                'id'       => $question->entityId(),
                'ordering' => $question->ordering,
                'answers'  => $question->answers->map(function (Answer $answer) {
                    return [
                        'id'         => $answer->entityId(),
                        'ordering'   => $answer->ordering,
                        'is_correct' => $answer->is_correct,
                        'answer'     => $answer->answer,
                    ];
                }),
            ];
        });

        $file     = null;
        $imageId  = $this->resource->image_file_id;
        $quizText = $this->resource->quizText;
        $text     = htmlspecialchars_decode($quizText->text_parsed);
        $privacy  = $this->resource->privacy;

        if ($imageId) {
            $file = [
                'itemId' => $imageId,
            ];
        }

        if ($privacy == MetaFoxPrivacy::CUSTOM) {
            $lists = PrivacyPolicy::getPrivacyItem($this->resource);

            $listIds = [];
            if (!empty($lists)) {
                $listIds = array_column($lists, 'item_id');
            }

            $privacy = $listIds;
        }

        $this
            ->title(__p('quiz::phrase.edit_quiz'))
            ->action(url_utility()->makeApiUrl('/quiz/' . $this->resource->entityId()))
            ->asPut()
            ->setValue([
                'title'     => $this->resource->title ?? '',
                'text'      => $quizText != null ? strip_tags($text, ['img', 'a']) : '',
                'questions' => $questions,
                'privacy'   => $privacy,
                'owner_id'  => $this->resource->owner_id,
                'file'      => $file,
            ]);
    }
}
