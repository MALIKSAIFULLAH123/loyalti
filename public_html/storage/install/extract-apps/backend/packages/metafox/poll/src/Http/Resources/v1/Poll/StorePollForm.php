<?php

namespace MetaFox\Poll\Http\Resources\v1\Poll;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Carbon;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\PrivacyFieldTrait;
use MetaFox\Form\Section;
use MetaFox\Platform\Contracts\User as UserContract;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Poll\Http\Requests\v1\Poll\CreateFormRequest;
use MetaFox\Poll\Http\Requests\v1\Poll\StoreRequest;
use MetaFox\Poll\Models\Poll as Model;
use MetaFox\Poll\Policies\PollPolicy;
use MetaFox\Poll\Repositories\PollRepositoryInterface;
use MetaFox\User\Models\User;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\Yup\ArrayShape;
use MetaFox\Yup\Yup;

/**
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @driverName poll.store
 * @driverType form
 */
class StorePollForm extends AbstractForm
{
    use PrivacyFieldTrait;

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function boot(CreateFormRequest $request, PollRepositoryInterface $repository, ?int $id = null): void
    {
        $context = user();
        $params  = $request->validated();
        if ($params['owner_id'] != 0) {
            $userEntity = UserEntity::getById($params['owner_id']);
            $this->setOwner($userEntity->detail);
        }

        app('quota')->checkQuotaControlWhenCreateItem($context, Model::ENTITY_TYPE, 1, ['messageFormat' => 'text']);
        policy_authorize(PollPolicy::class, 'create', $context, $this->owner);
        $this->resource = new Model($params);
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $context = user();
        $privacy = UserPrivacy::getItemPrivacySetting($context->entityId(), 'poll.item_privacy');
        if ($privacy === false) {
            $privacy = MetaFoxPrivacy::EVERYONE;
        }

        $minAnswers     = 2; //@todo: implement with setting
        $answersDefault = [];
        for ($i = 1; $i <= $minAnswers; $i++) {
            $answersDefault[] = [
                'answer' => '',
                'order'  => $i,
            ];
        }

        $this->title(__p('poll::phrase.new_poll_title'))
            ->action(url_utility()->makeApiUrl('/poll'))
            ->asPost()
            ->setBackProps(__p('core::web.polls'))
            ->setValue([
                'is_multiple'  => 0,
                'enable_close' => 0,
                'public_vote'  => 1,
                'privacy'      => $privacy,
                'answers'      => $answersDefault,
                'owner_id'     => $this->resource->owner_id,
                'attachments'  => [],
                'has_banner'   => 0,
                'question'     => '',
                'close_time'   => null,
            ]);
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
        $context           = user();
        $basic             = $this->addBasic();
        $maxQuestionLength = Settings::get('poll.maximum_name_length', 100);
        $minQuestionLength = Settings::get('poll.minimum_name_length', MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH);

        $basic->addFields(
            Builder::text('question')
                ->required()
                ->returnKeyType('next')
                ->label(__p('poll::phrase.poll_question'))
                ->placeholder(__p('poll::phrase.fill_in_a_question'))
                ->description(__p(
                    'poll::phrase.the_maximum_number_of_characters_description',
                    ['number' => $maxQuestionLength]
                ))
                ->maxLength($maxQuestionLength)
                ->yup(
                    Yup::string()
                        ->required()
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
            Builder::pollAnswer('answers')
                ->required()
                ->label(__p('poll::phrase.poll_answer'))
                ->maxLength(StoreRequest::MAX_ANSWER_LENGTH)
                ->minAnswers(2)
                ->maxAnswers($this->getMaxAnswer())
                ->returnKeyType('next')
                ->yup($this->buildAnswerValidation()),
            $this->buildTextField(),
            Builder::attachment()->itemType('poll'),
            $this->buildPhotoField($context),
            Builder::checkbox('public_vote')
                ->multiple(false)
                ->label(__p('poll::phrase.public_votes'))
                ->description(__p('poll::phrase.poll_description_public_votes'))
                ->valueType('array'),
            Builder::checkbox('is_multiple')
                ->multiple(false)
                ->label(__p('poll::phrase.allow_multiple_choice'))
                ->description(__p('poll::phrase.poll_description_allow_multiple_choice'))
                ->valueType('array'),
            Builder::checkbox('enable_close')
                ->multiple(false)
                ->label(__p('poll::phrase.set_close_time'))
                ->description(__p('poll::phrase.poll_description_closed_times')),
            Builder::pollCloseTime('close_time')
                ->requiredWhen(['truthy', 'enable_close'])
                ->nullable()
                ->returnKeyType('text')
                ->margin('normal')
                ->labelDatePicker(__p('core::phrase.date'))
                ->labelTimePicker(__p('poll::phrase.time'))
                ->timeSuggestion()
                ->showWhen(['truthy', 'enable_close'])
                ->yup(
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
                                        ->setError('typeError', __p('validation.date', ['attribute' => __p('poll::phrase.close_time')])),
                                )
                        )
                ),

            // Privacy field
            $this->buildPrivacyField()
                ->fullWidth(false)
                ->minWidth(275)
                ->description(__p('poll::phrase.control_who_can_see_this_poll')),

            // Hidden fields
            Builder::hidden('owner_id'),
            Builder::hidden('has_banner'),
        );

        $footer = $this->addFooter();

        $this->setButtonFields($footer);
    }

    protected function setButtonFields(Section $footer): void
    {
        $footer->addFields(
            Builder::submit('submit')
                ->label(__p('core::phrase.submit'))
                ->setValue(1),
            Builder::cancelButton()->sizeMedium(),
        );
    }

    protected function buildPhotoField(UserContract $user): ?AbstractField
    {
        $canUploadImage = $user->hasPermissionTo('poll.upload_image');

        if (!$canUploadImage) {
            return null;
        }

        return Builder::singlePhoto()
            ->widthPhoto('160px')
            ->aspectRatio('1:1')
            ->required(Settings::get('poll.is_image_required', false))
            ->itemType('poll')
            ->thumbnailSizes($this->resource->getSizes())
            ->previewUrl($this->resource->image)
            ->yup(
                Yup::object()
                    ->nullable()
                    ->addProperty('id', [
                        'type'     => 'number',
                        'required' => Settings::get('poll.is_image_required', false),
                        'errors'   => [
                            'required' => __p('photo::validation.photo_is_a_required_field'),
                        ],
                    ])
            );
    }

    protected function buildAnswerValidation(): ArrayShape
    {
        $context    = user();
        $maxAnswers = $this->getMaxAnswer();

        $yup = Yup::array()
            ->min(2)
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
            );

        if (!$context->hasSuperAdminRole()) {
            $yup->max($maxAnswers)
                ->setError('max', __p('validation.max.array', [
                    'attribute' => 'answers',
                    'max'       => (int) $context->getPermissionValue('poll.maximum_answers_count') ?? 2,
                ]));
        }

        return $yup;
    }

    protected function getMaxAnswer(): int
    {
        $context = user();

        if ($context->hasSuperAdminRole()) {
            return 0;
        }

        return (int) $context->getPermissionValue('poll.maximum_answers_count') ?? 2;
    }

    protected function buildTextField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);
        if ($settingAllowHtml) {
            return Builder::richTextEditor('text')
                ->returnKeyType('default')
                ->label(__p('core::phrase.description'))
                ->placeholder(__p('core::phrase.add_some_description_to_your_type', ['type' => 'poll']));
        }

        return Builder::textArea('text')
            ->returnKeyType('default')
            ->label(__p('core::phrase.description'))
            ->placeholder(__p('core::phrase.add_some_description_to_your_type', ['type' => 'poll']));
    }
}
