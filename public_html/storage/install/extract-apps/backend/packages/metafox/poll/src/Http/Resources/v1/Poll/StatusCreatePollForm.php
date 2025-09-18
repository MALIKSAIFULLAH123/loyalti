<?php

namespace MetaFox\Poll\Http\Resources\v1\Poll;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ResourceNameRule;
use MetaFox\Platform\Rules\UniqueValueInArray;
use MetaFox\Poll\Http\Requests\v1\Poll\StoreRequest;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Models\Poll as Model;
use MetaFox\Poll\Repositories\PollRepositoryInterface;
use MetaFox\User\Models\User;
use MetaFox\Yup\Yup;

/**
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StatusCreatePollForm extends AbstractForm
{
    /**
     * @var bool
     */
    protected $isEdit;

    /**
     * @var bool
     */
    protected $isEditSchedule;

    public function __construct($resource = null, bool $isEdit = false, bool $isEditSchedule = false)
    {
        parent::__construct($resource);

        $this->isEdit         = $isEdit;
        $this->isEditSchedule = $isEditSchedule;
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $this->title(__p('poll::phrase.new_poll_title'))
            ->submitAction('@poll/statusAddPoll')
            ->action('/poll')
            ->asPost()
            ->setValue([
                'poll_multiple'    => 0,
                'enable_close'     => 0,
                'poll_public'      => 1,
                'poll_answers'     => [],
                'poll_question'    => '',
                'poll_attachments' => [],
            ]);

        if ($this->isEdit) {
            $this->title(__p('poll::phrase.edit_poll_title'));
        }
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws AuthenticationException
     */
    public function initialize(): void
    {
        /** @var User $context */
        $context = user();

        $header = $this->addSection(
            Builder::section('header')
                ->component(MetaFoxForm::DIALOG_HEADER)
                ->setAttribute('config', [
                    'disableClose' => true,
                ])
        );

        $header->addFields(
            Builder::cancelButton()
                ->variant('text'),
            Builder::submit('submit')
                ->component(MetaFoxForm::BUTTON)
                ->label($this->isEdit ? __p('core::phrase.save_changes') : __p('core::phrase.create'))
                ->variant('text')
                ->setValue(1)
        );

        $maxAnswers        = $context->getPermissionValue('poll.maximum_answers_count') ?? 2;
        $maxQuestionLength = Settings::get('poll.maximum_name_length', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);
        $minQuestionLength = Settings::get('poll.minimum_name_length', MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH);

        $basic = $this->addBasic();
        $basic->addFields(
            Builder::text('poll_question')
                ->required()
                ->returnKeyType('next')
                ->label(__p('poll::phrase.poll_question'))
                ->placeholder(__p('poll::phrase.fill_in_a_question'))
                ->maxLength($maxQuestionLength)
                ->yup(
                    Yup::string()->required()
                        ->minLength(
                            $minQuestionLength,
                            __p(
                                'poll::validation.question_minimum_length_of_characters',
                                ['number' => $minQuestionLength]
                            )
                        )
                        ->maxLength(
                            $maxQuestionLength,
                            __p('poll::validation.question_maximum_length_of_characters', [
                                'min' => $minQuestionLength,
                                'max' => $maxQuestionLength,
                            ])
                        )
                ),
            Builder::pollAnswer('poll_answers')
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
            Builder::checkbox('poll_public')
                ->label(__p('poll::phrase.public_votes'))
                ->description(__p('poll::phrase.poll_description_public_votes')),
            Builder::checkbox('poll_multiple')
                ->label(__p('poll::phrase.allow_multiple_choice'))
                ->description(__p('poll::phrase.poll_description_allow_multiple_choice')),
            Builder::checkbox('enable_close')
                ->label(__p('poll::phrase.set_close_time'))
                ->description(__p('poll::phrase.poll_description_closed_times')),
            Builder::pollCloseTime('poll_close_time')
                ->requiredWhen(['truthy', 'enable_close'])
                ->returnKeyType('next')
                ->nullable()
                ->margin('normal')
                ->timeSuggestion()
                ->labelDatePicker(__p('core::phrase.date'))
                ->labelTimePicker(__p('poll::phrase.time'))
                ->showWhen(['truthy', 'enable_close'])->yup(
                    Yup::date()
                        ->nullable()
                        ->when(
                            Yup::when('enable_close')
                                ->is(1)
                                ->then(
                                    Yup::date()
                                        ->nullable()
                                        ->required(__p('poll::phrase.close_time_is_a_required_field'))
                                        ->min(Carbon::now(), __p('poll::phrase.the_close_time_should_be_greater_than_the_current_time'))
                                        ->setError('typeError', __p('poll::phrase.close_time_is_a_required_field')),
                                )
                        )
                ),
        );
    }

    /**
     * @param  Request             $request
     * @return array
     * @throws ValidationException
     */
    public function validated(Request $request): array
    {
        $rules = $this->getValidationRules();

        $data = $request->all();

        if (count($rules)) {
            $rules = $this->getValidationRules();

            $validator = Validator::make($data, $rules);

            $data = array_merge($validator->validated(), [
                'user_status' => Arr::get($data, 'user_status', ''),
            ]);
        }
        $data['attachments'] = $data['poll_attachments'] ?? null;
        unset($data['poll_attachments']);

        return $this->transformData($data);
    }

    public function getValidationRules(): array
    {
        if ($this->isEdit && !$this->isEditSchedule) {
            return [];
        }

        return [
            'poll_question' => ['required_if:post_type,' . Poll::FEED_POST_TYPE, 'string', new ResourceNameRule('poll')],
            'poll_answers'  => [
                'required_if:post_type,' . Poll::FEED_POST_TYPE, 'array', 'min:2', new UniqueValueInArray(['answer']),
            ],
            'poll_answers.*.answer'     => ['required_if:post_type,' . Poll::FEED_POST_TYPE, 'string'],
            'poll_close_time'           => ['sometimes', 'date', 'nullable'],
            'poll_public'               => ['sometimes'],
            'poll_multiple'             => ['sometimes'],
            'enable_close'              => ['sometimes'],
            'poll_attachments'          => ['sometimes', 'array'],
            'poll_attachments.*.id'     => ['sometimes', 'numeric', 'exists:core_attachments,id'],
            'poll_attachments.*.status' => ['sometimes', 'string', new AllowInRule(['create', 'remove'])],
        ];
    }

    protected function transformData(array $data): array
    {
        $data = resolve(PollRepositoryInterface::class)->prepareDataForFeed($data);

        if ($this->isEdit && !$this->isEditSchedule) {
            // Only privacy, tagged friends and content are editable in poll's feed.
            unset($data['question']);
            unset($data['answers']);
        }

        return $data;
    }
}
