<?php

namespace MetaFox\Poll\Http\Resources\v1\Poll;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Poll\Http\Requests\v1\Poll\StoreRequest;
use MetaFox\Poll\Models\Poll as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class ActivityPollForm.
 * @property ?Model $resource
 */
class ActivityPollForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title(__p('poll::phrase.add_poll_title'))
            ->action(url_utility()->makeApiUrl('/feed'))
            ->asPost()
            ->setValue([
                //
            ]);
    }

    /**
     * @throws AuthenticationException
     */
    protected function initialize(): void
    {
        $context = user();
        $header  = $this->addSection(['name' => 'header']);
        $header->addField(
            Builder::submit('submit')->label(__p('core::phrase.done')),
        );

        $maxAnswers = $context->getPermissionValue('poll.maximum_answers_count') ?? 2;
        $basic      = $this->addBasic();
        $basic->addFields(
            Builder::text('poll_question')
                ->required()
                ->returnKeyType('next')
                ->label(__p('core::phrase.poll_question'))
                ->placeholder(__p('poll::phrase.fill_in_a_question'))
                ->yup(
                    Yup::string()->required()
                ),
            Builder::pollAnswer('answers')
                ->required()
                ->maxLength(StoreRequest::MAX_ANSWER_LENGTH)
                ->minAnswers(2)
                ->maxAnswers($maxAnswers)
                ->returnKeyType('next')
                ->yup(
                    Yup::array()
                        ->min(2)
                        ->max($maxAnswers)
                        ->uniqueBy('answer', __p('poll::validation.the_answers_must_be_unique'))
                        ->of(
                            Yup::object()
                                ->addProperty(
                                    'answer',
                                    Yup::string()
                                        ->required(__p('validation.field_is_a_required_field', [
                                            'field' => 'Answer',
                                        ]))
                                        ->minLength(StoreRequest::MIN_ANSWER_LENGTH)
                                        ->maxLength(StoreRequest::MAX_ANSWER_LENGTH)
                                        ->setError('minLength', __p('validation.min.string', [
                                            'attribute' => 'answer',
                                            'min'       => StoreRequest::MIN_ANSWER_LENGTH,
                                        ]))
                                        ->setError('maxLength', __p('validation.max.string', [
                                            'attribute' => 'answer',
                                            'max'       => StoreRequest::MAX_ANSWER_LENGTH,
                                        ]))
                                )
                        )
                ),
            Builder::pollCloseTime('close_time')
                ->required(false)
                ->returnKeyType('next')
                ->margin('normal')
                ->label(__p('core::phrase.expire_after'))
                ->labelDatePicker(__p('core::phrase.close_date'))
                ->labelTimePicker(__p('core::phrase.close_time'))
                ->timeSuggestion(true)
                ->showWhen(['truthy']),
            Captcha::getFormField('poll.create_poll')
        );
    }
}
