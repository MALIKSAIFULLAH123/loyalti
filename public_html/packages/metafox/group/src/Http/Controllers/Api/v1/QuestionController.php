<?php

namespace MetaFox\Group\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\ValidationException;
use MetaFox\Group\Http\Requests\v1\Question\FormRequest;
use MetaFox\Group\Http\Requests\v1\Question\IndexRequest;
use MetaFox\Group\Http\Requests\v1\Question\StoreAnswerRequest;
use MetaFox\Group\Http\Requests\v1\Question\StoreRequest;
use MetaFox\Group\Http\Requests\v1\Question\UpdateRequest;
use MetaFox\Group\Http\Resources\v1\Group\GroupDetail;
use MetaFox\Group\Http\Resources\v1\Question\JoinQuestionForm;
use MetaFox\Group\Http\Resources\v1\Question\QuestionItem;
use MetaFox\Group\Http\Resources\v1\Question\QuestionItemCollection as ItemCollection;
use MetaFox\Group\Http\Resources\v1\Question\StoreQuestionForm;
use MetaFox\Group\Http\Resources\v1\Question\UpdateQuestionForm;
use MetaFox\Group\Models\Invite;
use MetaFox\Group\Models\Member;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Repositories\InviteRepositoryInterface;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Repositories\QuestionRepositoryInterface;
use MetaFox\Group\Repositories\RuleRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Group\Http\Controllers\Api\QuestionController::$controllers.
 */

/**
 * Class GroupQuestionController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 * @group group
 * @authenticated
 */
class QuestionController extends ApiController
{
    /**
     * @var QuestionRepositoryInterface
     */
    private QuestionRepositoryInterface $repository;
    /**
     * @var GroupRepositoryInterface
     */
    private GroupRepositoryInterface $groupRepository;
    /**
     * @var RuleRepositoryInterface
     */
    private RuleRepositoryInterface $groupRuleRepository;
    /**
     * @var MemberRepositoryInterface
     */
    private MemberRepositoryInterface $memberRepository;

    /**
     * QuestionController constructor.
     *
     * @param QuestionRepositoryInterface $repository
     * @param GroupRepositoryInterface    $groupRepository
     * @param RuleRepositoryInterface     $groupRuleRepository
     * @param MemberRepositoryInterface   $memberRepository
     */
    public function __construct(
        QuestionRepositoryInterface $repository,
        GroupRepositoryInterface $groupRepository,
        RuleRepositoryInterface $groupRuleRepository,
        MemberRepositoryInterface $memberRepository
    ) {
        $this->repository          = $repository;
        $this->groupRepository     = $groupRepository;
        $this->groupRuleRepository = $groupRuleRepository;
        $this->memberRepository    = $memberRepository;
    }

    /**
     * Browse Question.
     *
     * @param IndexRequest $request
     *
     * @return JsonResource
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();

        $data = $this->repository->getQuestions(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Store a question.
     *
     * @param StoreRequest $request
     *
     * @return QuestionItem
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $group = $this->groupRepository->find($params['group_id']);

        policy_authorize(GroupPolicy::class, 'addMembershipQuestion', $context, $group);

        $question = $this->repository->createQuestion($context, $params);

        return $this->success(
            new QuestionItem($question),
            [],
            __p('group::phrase.the_membership_question_has_been_created_successfully')
        );
    }

    /**
     * Update question.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return QuestionItem
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $data = $this->repository->updateQuestion(user(), $id, $params);

        return $this->success(
            new QuestionItem($data),
            [],
            __p('group::phrase.the_membership_question_has_been_updated_successfully')
        );
    }

    /**
     * Remove question.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException|AuthorizationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteQuestion(user(), $id);

        return $this->success([
            'id' => $id,
        ], [], __p('group::phrase.the_membership_question_has_been_deleted_successfully'));
    }

    /**
     * Get creation form.
     *
     * @param FormRequest $request
     * @param int|null    $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function form(FormRequest $request, ?int $id = null): JsonResponse
    {
        $data = $request->validated();

        $groupId = $data['group_id'];

        $question = null;

        $isEdit = false;

        if (null !== $id) {
            $question = $this->repository->find($id);

            $groupId = $question->group_id;

            $isEdit = true;
        }

        $group = resolve(GroupRepositoryInterface::class)->find($groupId);

        $context = user();

        switch ($isEdit) {
            case true:
                policy_authorize(GroupPolicy::class, 'manageMembershipQuestion', $context, $group);

                $form = new UpdateQuestionForm($groupId, $question);

                break;
            default:
                if (!policy_check(GroupPolicy::class, 'addMembershipQuestion', $context, $group)) {
                    return $this->error(__p('group::phrase.you_have_reached_your_limit_to_add_new_question'), 403);
                }

                $form = new StoreQuestionForm($groupId);

                break;
        }

        return $this->success($form);
    }

    /**
     * Get answer form.
     *
     * @param int $id
     *
     * @return JsonResponse|JoinQuestionForm
     */
    public function answerForm(int $id)
    {
        $group = $this->groupRepository->find($id);

        if (false == $this->groupRepository->hasMembershipQuestion($group)) {
            return $this->success();
        }

        return new JoinQuestionForm($group);
    }

    /**
     * Create a new answer.
     *
     * @param StoreAnswerRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function createAnswer(StoreAnswerRequest $request): JsonResponse
    {
        $data = $request->all();

        $group     = $this->groupRepository->getGroup($data['group_id']);
        $result    = $this->memberRepository->createRequest(user(), $data['group_id']);
        $requestId = $result['data']['request_id'];

        if (array_key_exists('question', $data)) {
            $this->repository->createAnswer(user(), $data['question'], $requestId);
        }

        if (!policy_check(GroupPolicy::class, 'answerQuestionBeforeJoining', $group)) {
            return $this->success();
        }

        $context = user();

        $inviteRepository = resolve(InviteRepositoryInterface::class);

        $invite = $inviteRepository->getPendingInvite($group->entityId(), $context);

        $isInvited = $invite instanceof Invite;

        if ($isInvited === true) {
            $result = $inviteRepository->acceptInvite($group, $context);

            if (false == $result) {
                return $this->error(__p('validation.something_went_wrong_please_try_again'), 403);
            }

            return $this->success(new GroupDetail($group), [], __p('group::phrase.you_joined', ['group' => $group->toTitle()]));
        }

        return $this->success(new GroupDetail($group), [], $result['message']);
    }
}
