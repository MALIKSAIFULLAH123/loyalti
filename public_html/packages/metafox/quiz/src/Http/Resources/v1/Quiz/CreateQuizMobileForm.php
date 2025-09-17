<?php

namespace MetaFox\Quiz\Http\Resources\v1\Quiz;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\QuizQuestionField;
use MetaFox\Form\PrivacyFieldMobileTrait;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Quiz\Http\Requests\v1\Quiz\CreateFormRequest;
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
class CreateQuizMobileForm extends AbstractForm
{
    use PrivacyFieldMobileTrait;

    /**
     * @throws AuthenticationException
     * @throws AuthorizationException
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

    protected function prepare(): void
    {
        $context = user();
        $privacy = UserPrivacy::getItemPrivacySetting($context->entityId(), 'quiz.item_privacy');
        if ($privacy === false) {
            $privacy = MetaFoxPrivacy::EVERYONE;
        }

        $minQuestions     = (int) $context->getPermissionValue('quiz.min_question_quiz');
        $defaultAnswers   = (int) $context->getPermissionValue('quiz.number_of_answers_per_default');
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
            ->asPost()->setValue([
                'questions' => $questionsDefault,
                'title'     => $this->resource->title ?? '',
                'text'      => $this->resource->quizText->text ?? '',
                'privacy'   => $privacy,
                'owner_id'  => $this->resource->owner_id,
            ]);
    }

    protected function initialize(): void
    {
        $basic          = $this->addBasic();
        $titleMaxLength = Settings::get('quiz.maximum_name_length', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);
        $titleMinLength = Settings::get('quiz.minimum_name_length', MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH);

        $basic->addFields(
            Builder::text('title')->required(true)
                ->returnKeyType('next')
                ->label(__p('core::phrase.title'))
                ->maxLength($titleMaxLength)
                ->description(__p('core::phrase.maximum_length_of_characters', ['length' => $titleMaxLength]))
                ->placeholder(__p('quiz::phrase.fill_in_a_title_for_your_quiz'))
                ->yup(
                    Yup::string()
                        ->required()
                        ->maxLength($titleMaxLength)
                        ->minLength($titleMinLength)
                ),
            $this->buildBannerField(),
            $this->buildQuizTextField(),
            $this->buildQuizQuestionField(),
            $this->buildPrivacyField()
                ->description(__p('quiz::phrase.control_who_can_see_this_quiz'))
                ->minWidth(275)
                ->fullWidth(false),
            Builder::hidden('owner_id')
        );
    }

    protected function buildQuizQuestionField(): AbstractField
    {
        $context = user();

        $maxLengthQuestion = Settings::get('quiz.max_length_quiz_question', 100);
        $minLengthQuestion = Settings::get('quiz.min_length_quiz_question', 4);
        $minQuestion       = (int) $context->getPermissionValue('quiz.min_question_quiz');
        $minAnswer         = (int) $context->getPermissionValue('quiz.min_answer_question_quiz');
        $maxAnswer         = (int) $context->getPermissionValue('quiz.max_answer_question_quiz');
        $maxQuestion       = (int) $context->getPermissionValue('quiz.max_question_quiz');
        $maxLengthAnswer   = Settings::get('quiz.maximum_quiz_answer_length', 255);
        $minLengthAnswer   = Settings::get('quiz.minimum_quiz_answer_length', 3);

        $questionAttributes = [
            'name'       => 'questions',
            'label'      => __p('quiz::phrase.question'),
            'minLength'  => $minLengthQuestion,
            'maxLength'  => $maxLengthQuestion,
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
                            'minLength' => $minLengthQuestion,
                            'maxLength' => $maxLengthQuestion,
                            'errors'    => [
                                'required'  => __p('quiz::validation.question_is_a_required_field'),
                                'minLength' => __p('quiz::validation.question_min_length', ['number' => $minLengthQuestion]),
                                'maxLength' => __p('quiz::validation.question_max_length', ['number' => $maxLengthQuestion]),
                            ],
                        ],
                        'answers'  => [
                            'type'     => 'array',
                            'required' => true,
                            'min'      => $minAnswer,
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
                                'errors'     => [
                                    'uniqueBy' => __p('quiz::validation.the_answers_list_must_be_unique'),
                                ],
                            ],
                            'errors'   => [
                                'required' => __p('quiz::validation.answer_is_a_required_field'),
                                'min'      => __p('validation.min.array', [
                                    'attribute' => 'answers',
                                    'min'       => $minAnswer,
                                ]),
                            ],
                        ],
                    ],
                    'errors'     => [
                        'uniqueBy' => __p('quiz::validation.the_question_list_must_be_unique'),
                    ],
                ],
                'errors'   => [
                    'min'      => __p('quiz::phrase.min_question_per_quiz_number', ['number' => $minQuestion]),
                    'required' => __p('quiz::validation.question_is_a_required_field'),
                ],
            ],
        ];

        if (!$context->hasSuperAdminRole()) {
            $questionAttributes['validation']['max']                                          = $maxQuestion;
            $questionAttributes['validation']['errors']['max']                                = __p('quiz::phrase.max_question_per_quiz_number', ['number' => $maxQuestion]);
            $questionAttributes['validation']['of']['properties']['answers']['max']           = $maxAnswer;
            $questionAttributes['validation']['of']['properties']['answers']['errors']['max'] = __p('validation.max.array', [
                'attribute' => 'answers',
                'max'       => $maxAnswer,
            ]);
        }

        return new QuizQuestionField($questionAttributes);
    }

    protected function buildBannerField()
    {
        $context = user();
        if ($context->hasPermissionTo('quiz.upload_photo_form')) {
            return Builder::singlePhoto('file')
                ->itemType('quiz')
                ->label(__p('Banner'))
                ->previewUrl($this->resource->image)
                ->thumbnailSizes($this->resource->getSizes())
                ->required($context->hasPermissionTo('quiz.require_upload_photo'))
                ->yup(
                    Yup::object()
                        ->nullable()
                        ->addProperty('itemId', [
                            'type'     => 'number',
                            'required' => $context->hasPermissionTo('quiz.require_upload_photo'),
                            'errors'   => [
                                'required' => __p('quiz::phrase.banner_is_a_required_field'),
                            ],
                        ])
                );
        }
    }

    protected function buildQuizTextField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);
        if ($settingAllowHtml) {
            return Builder::richTextEditor('text')
                ->required(true)
                ->returnKeyType('default')
                ->label(__p('core::phrase.description'))
                ->placeholder(__p('quiz::phrase.quiz_text_description'))
                ->yup(Yup::string()->required());
        }

        return Builder::textArea('text')
            ->required(true)
            ->returnKeyType('default')
            ->label(__p('core::phrase.description'))
            ->placeholder(__p('quiz::phrase.quiz_text_description'))
            ->yup(Yup::string()->required());
    }
}
