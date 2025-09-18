<?php

namespace MetaFox\Poll\Http\Resources\v1\Poll;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Carbon;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\PrivacyFieldMobileTrait;
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
 *
 * @driverType form-mobile
 * @driverName poll.poll.store
 */
class StorePollMobileForm extends AbstractForm
{
    use PrivacyFieldMobileTrait;

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
            ->setValue([
                'is_multiple'  => 0,
                'enable_close' => 0,
                'public_vote'  => 1,
                'privacy'      => $privacy,
                'answers'      => $answersDefault,
                'owner_id'     => $this->resource->owner_id,
                'has_banner'   => 0,
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
        $maxAnswers        = $context->getPermissionValue('poll.maximum_answers_count') ?? 2;
        $minQuestionLength = Settings::get('poll.minimum_name_length', MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH);
        $maxQuestionLength = Settings::get('poll.maximum_name_length', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);

        $basic->addFields(
            Builder::text('question')
                ->required()
                ->returnKeyType('next')
                ->label(__p('poll::phrase.poll_question'))
                ->placeholder(__p('poll::phrase.fill_in_a_question'))
                ->maxLength($maxQuestionLength)
                ->description(__p(
                    'poll::phrase.the_maximum_number_of_characters_description',
                    ['number' => $maxQuestionLength]
                ))
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
                ->returnKeyType('next')
                ->maxLength(StoreRequest::MAX_ANSWER_LENGTH)
                ->minAnswers(2)
                ->maxAnswers($this->getMaxAnswer())
                ->yup($this->buildAnswerValidation()),
            $this->buildTextField(),
            $this->buildPhotoField($context),
            Builder::checkbox('public_vote')
                ->multiple(false)
                ->label(__p('poll::phrase.public_votes'))
                ->valueType('array'),
            Builder::checkbox('is_multiple')
                ->multiple(false)
                ->label(__p('poll::phrase.allow_multiple_choice'))
                ->valueType('array'),
            Builder::switch('enable_close')
                ->multiple(false)
                ->label(__p('poll::phrase.set_close_time')),
            Builder::pollCloseTime('close_time')
                ->label(__p('poll::phrase.close_time'))
                ->requiredWhen(['eq', 'enable_close', 1])
                ->returnKeyType('text')
                ->margin('normal')
                ->showWhen(['eq', 'enable_close', 1])
                ->minDate(Carbon::now())
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->when(
                            Yup::when('enable_close')
                                ->is(1)
                                ->then(
                                    Yup::date()
                                        ->required(__p('poll::phrase.close_time_is_a_required_field'))
                                        ->min(Carbon::now(), __p('poll::phrase.the_close_time_should_be_greater_than_the_current_time'))
                                        ->setError('typeError', __p('poll::phrase.close_time_is_a_required_field')),
                                )
                        )
                ),
            $this->buildPrivacyField()
                ->description(__p('poll::phrase.control_who_can_see_this_poll'))
                ->fullWidth(false)
                ->setAttributes([
                    'minWidth'  => 275,
                    'maxHeight' => 40,
                ]),
            Builder::hidden('owner_id'),
            Builder::hidden('has_banner'),
        );
    }

    protected function buildPhotoField(UserContract $user): ?AbstractField
    {
        $canUploadImage = $user->hasPermissionTo('poll.upload_image');

        if (!$canUploadImage) {
            return null;
        }

        $isRequireImage = Settings::get('poll.is_image_required', false);

        $photoField = Builder::singlePhoto('file')
            ->required($isRequireImage)
            ->itemType('poll')
            ->label(__p('poll::phrase.poll_photo'))
            ->previewUrl($this->resource->image);

        if (!$isRequireImage) {
            return $photoField;
        }

        return $photoField->yup(
            Yup::object()
                ->nullable()
                ->required(__p('photo::validation.photo_is_a_required_field'))
        );
    }

    /**
     * @throws AuthenticationException
     */
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

    /**
     * @throws AuthenticationException
     */
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
