<?php

namespace MetaFox\Group\Http\Resources\v1\Request;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Http\Resources\v1\Group\GroupPreview;
use MetaFox\Group\Models\Question;
use MetaFox\Group\Models\QuestionField;
use MetaFox\Group\Models\Request as Model;
use MetaFox\Group\Repositories\QuestionRepositoryInterface;
use MetaFox\Group\Support\ResourcePermission;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\ResourcePermission as ACL;
use MetaFox\User\Models\UserEntity;

/**
 * Class RequestItem.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class RequestItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $answers = resolve(QuestionRepositoryInterface::class)
            ->getAnswersByRequestId(user(), $this->resource);

        $answers  = $this->transformValue($answers);
        $reviewer = $this->resource?->reviewerEntity instanceof UserEntity
            ? ResourceGate::user($this->resource->reviewerEntity)
            : null;

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'group',
            'resource_name' => $this->resource->entityType(),
            'user'          => ResourceGate::user($this->resource->userEntity),
            'reviewer'      => $reviewer,
            'group_id'      => $this->resource->group_id,
            'status_text'   => $this->resource->statusText(),
            'created_at'    => $this->resource->created_at,
            'updated_at'    => $this->resource->updated_at,
            'answers'       => $answers,
            'reason'        => $this->resource->reason,
            'extra'         => $this->getRequestExtra(),
            'group'         => new GroupPreview($this->resource->group),
        ];
    }

    private function getRequestExtra(): array
    {
        $user = user();

        return [
            ACL::CAN_APPROVE                    => $user->can('approve', [$this->resource, $this->resource]),
            ResourcePermission::CAN_VIEW_REASON => $this->resource->isDenied() && !empty($this->resource->reason),
        ];
    }

    private function transformValue(Collection $collection): array
    {
        foreach ($collection as $key => $value) {
            $answers = $value['answers'];

            if (!$answers->count()) {
                unset($collection[$key]);
                continue;
            }

            foreach ($answers as $keyAnswer => $answer) {
                /** @var Question $question */
                $question = Question::query()
                    ->with(['questionFields'])
                    ->where('id', $answer->question_id)->first();

                if ($question->type_id == Question::TYPE_TEXT) {
                    continue;
                }

                /** @var QuestionField $questionField */
                $questionField                = $question->questionFields->where('id', $answer->value)->first();
                $answers[$keyAnswer]['value'] = $questionField->title;
            }

            $collection[$key]['answers'] = $answers;
        }

        return $collection->toArray();
    }
}
