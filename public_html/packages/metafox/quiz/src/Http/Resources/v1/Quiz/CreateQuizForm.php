<?php

namespace MetaFox\Quiz\Http\Resources\v1\Quiz;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\PrivacyFieldTrait;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Quiz\Http\Requests\v1\Quiz\CreateFormRequest;
use MetaFox\Quiz\Http\Resources\v1\Quiz\Field\QuizQuestion;
use MetaFox\Quiz\Models\Quiz as Model;
use MetaFox\Quiz\Policies\QuizPolicy;
use MetaFox\Quiz\Repositories\QuizRepositoryInterface;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\Yup\Yup;

/**
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateQuizForm extends AbstractForm
{
    use PrivacyFieldTrait;

    protected bool $isEdit = false;

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(CreateFormRequest $request, QuizRepositoryInterface $repository, ?int $id = null): void
    {
        $context = user();
        $params  = $request->validated();

        if ($params['owner_id'] != 0) {
            $userEntity = UserEntity::getById($params['owner_id']);
            $this->setOwner($userEntity->detail);
        }

        app('quota')->checkQuotaControlWhenCreateItem($context, Model::ENTITY_TYPE, 1, ['messageFormat' => 'text']);
        policy_authorize(QuizPolicy::class, 'create', $context, $this->owner);
        $this->resource = new Model($params);
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $context = user();
        $privacy = UserPrivacy::getItemPrivacySetting($context->entityId(), 'quiz.item_privacy');
        if ($privacy === false) {
            $privacy = MetaFoxPrivacy::EVERYONE;
        }

        $minQuestions   = (int) $context->getPermissionValue('quiz.min_question_quiz');
        $defaultAnswers = (int) $context->getPermissionValue('quiz.number_of_answers_per_default');

        $questionsDefault = [];
        //factory data
        for ($i = 1; $i <= $minQuestions; $i++) {
            $answers = [];
            for ($j = 1; $j <= $defaultAnswers; $j++) {
                $isCorrect = 0;
                if ($j == 1) {
                    $isCorrect = 1;
                }

                $answers[] = ['answer' => '', 'is_correct' => $isCorrect, 'ordering' => $j];
            }

            $questionsDefault[] = [
                'question' => '',
                'ordering' => $i,
                'answers'  => $answers,
            ];
        }

        $this->title(__p('quiz::phrase.add_new_quiz'))
            ->action(url_utility()->makeApiUrl('/quiz'))
            ->setBackProps(__p('core::web.quiz'))
            ->asPost()->setValue([
                'questions'   => $questionsDefault,
                'title'       => $this->resource->title ?? '',
                'text'        => $this->resource->quizText->text ?? '',
                'attachments' => [],
                'privacy'     => $privacy,
                'owner_id'    => $this->resource->owner_id,
            ]);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function initialize(): void
    {
        $context           = user();
        $basic             = $this->addBasic();
        $minQuestion       = (int) $context->getPermissionValue('quiz.min_question_quiz');
        $maxQuestion       = (int) $context->getPermissionValue('quiz.max_question_quiz');
        $titleMaxLength    = Settings::get('quiz.maximum_name_length', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);
        $titleMinLength    = Settings::get('quiz.minimum_name_length', MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH);
        $questionMaxLength = Settings::get('quiz.max_length_quiz_question', 100);
        $questionMinLength = Settings::get('quiz.min_length_quiz_question', 4);
        $maxLengthAnswer   = Settings::get('quiz.maximum_quiz_answer_length', 255);
        $minLengthAnswer   = Settings::get('quiz.minimum_quiz_answer_length', 3);

        $questionField = [
            'name'       => 'questions',
            'minLength'  => $questionMinLength,
            'maxLength'  => $questionMaxLength,
            'validation' => [
                'type'     => 'array',
                'required' => true,
                'min'      => $minQuestion,
                'of'       => [
                    'type'       => 'object',
                    'uniqueBy'   => 'question',
                    'properties' => [
                        'question' => [
                            'type'      => 'string',
                            'label'     => 'Question',
                            'required'  => true,
                            'minLength' => $questionMinLength,
                            'maxLength' => $questionMaxLength,
                            'errors'    => [
                                'required'  => __p('quiz::validation.question_is_a_required_field'),
                                'minLength' => __p('quiz::validation.question_min_length', ['number' => $questionMinLength]),
                                'maxLength' => __p('quiz::validation.question_max_length', ['number' => $questionMaxLength]),
                            ],
                        ],
                        'answers' => [
                            'type'     => 'array',
                            'required' => true,
                            'min'      => (int) $context->getPermissionValue('quiz.min_answer_question_quiz'),
                            'of'       => [
                                'type'       => 'object',
                                'uniqueBy'   => 'answer',
                                'properties' => [
                                    'answer' => [
                                        'type'      => 'string',
                                        'required'  => true,
                                        'maxLength' => $maxLengthAnswer,
                                        'minLength' => $minLengthAnswer,
                                        'errors'    => [
                                            'required'  => __p('quiz::validation.answer_is_a_required_field'),
                                            'maxLength' => __p(
                                                'validation.field_must_be_at_most_max_length_characters',
                                                [
                                                    'field'     => __p('quiz::phrase.answer'),
                                                    'maxLength' => $maxLengthAnswer,
                                                ]
                                            ),
                                            'minLength' => __p(
                                                'validation.field_must_be_at_least_min_length_characters',
                                                [
                                                    'field'     => __p('quiz::phrase.answer'),
                                                    'minLength' => $minLengthAnswer,
                                                ]
                                            ),
                                        ],
                                    ],
                                ],
                                'errors' => [
                                    'uniqueBy' => __p('quiz::validation.the_answers_list_must_be_unique'),
                                ],
                            ],
                            'errors' => [
                                'required' => __p('quiz::validation.answer_is_a_required_field'),
                                'min'      => __p('validation.min.array', [
                                    'attribute' => 'answers',
                                    'min'       => (int) $context->getPermissionValue('quiz.min_answer_question_quiz'),
                                ]),
                            ],
                        ],
                    ],
                    'errors' => [
                        'uniqueBy' => __p('quiz::validation.the_question_list_must_be_unique'),
                    ],
                ],
                'errors' => [
                    'min' => __p('quiz::phrase.min_question_per_quiz_number', ['number' => $minQuestion]),
                ],
            ],
        ];

        if (!$context->hasSuperAdminRole()) {
            $questionField['validation']['max']                                          = $maxQuestion;
            $questionField['validation']['errors']['max']                                = __p('quiz::phrase.max_question_per_quiz_number', ['number' => $maxQuestion]);
            $questionField['validation']['of']['properties']['answers']['max']           = (int) $context->getPermissionValue('quiz.max_answer_question_quiz');
            $questionField['validation']['of']['properties']['answers']['errors']['max'] = __p('validation.max.array', [
                'attribute' => 'answers',
                'max'       => (int) $context->getPermissionValue('quiz.max_answer_question_quiz'),
            ]);
        }

        $basic->addFields(
            Builder::text('title')
                ->required(true)
                ->returnKeyType('next')
                ->maxLength($titleMaxLength)
                ->margin('normal')->label(__p('core::phrase.title'))
                ->placeholder(__p('quiz::phrase.fill_in_a_title_for_your_quiz'))
                ->description(__p('core::phrase.maximum_length_of_characters', ['length' => $titleMaxLength]))
                ->yup(
                    Yup::string()
                        ->required()
                        ->maxLength($titleMaxLength)
                        ->minLength($titleMinLength)
                ),
            $this->buildBannerField($context),
            $this->buildQuizTextField(),
            Builder::attachment()
                ->placeholder(__p('core::phrase.attach_files'))
                ->itemType('quiz'),
            new QuizQuestion($questionField),
            $this->buildPrivacyField()
                ->description(__p('quiz::phrase.control_who_can_see_this_quiz'))
                ->minWidth(275)
                ->fullWidth(false),
            Builder::hidden('owner_id')
        );

        $this->addDefaultFooter();
    }

    protected function buildBannerField(User $user)
    {
        $canUploadImage     = $user->hasPermissionTo('quiz.upload_photo_form');
        $requireUploadImage = $user->hasPermissionTo('quiz.require_upload_photo');

        if (!$canUploadImage) {
            return null;
        }

        return Builder::singlePhoto()->label(__p('core::phrase.banner'))
            ->widthPhoto('160px')
            ->aspectRatio('1:1')
            ->itemType('quiz')
            ->required($requireUploadImage)
            ->previewUrl($this->resource->image)
            ->thumbnailSizes($this->resource->getSizes())
            ->yup(
                Yup::object()
                    ->nullable()
                    ->addProperty('id', [
                        'type'     => 'number',
                        'required' => $requireUploadImage,
                        'errors'   => [
                            'required' => __p('quiz::phrase.banner_is_a_required_field'),
                        ],
                    ])
            );
    }

    protected function buildQuizTextField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);
        if ($settingAllowHtml) {
            return Builder::richTextEditor('text')
                ->required()
                ->returnKeyType('default')
                ->label(__p('core::phrase.description'))
                ->placeholder(__p('quiz::phrase.quiz_text_description'))
                ->yup(Yup::string()->required());
        }

        return Builder::textArea('text')
            ->required()
            ->returnKeyType('default')
            ->label(__p('core::phrase.description'))
            ->placeholder(__p('quiz::phrase.quiz_text_description'))
            ->yup(Yup::string()->required());
    }
}
