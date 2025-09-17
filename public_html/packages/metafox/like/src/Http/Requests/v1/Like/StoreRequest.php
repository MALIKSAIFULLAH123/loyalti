<?php

namespace MetaFox\Like\Http\Requests\v1\Like;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Like\Models\Reaction;
use MetaFox\Like\Support\Facades\MobileAppAdapter;
use MetaFox\Platform\MetaFox;

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $ruleReaction = ['sometimes', 'numeric', 'exists:like_reactions,id'];

        if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.9', '<')) {
            $ruleReaction = ['sometimes', 'numeric'];
        }

        return [
            'item_id'     => ['required', 'numeric'],
            'item_type'   => ['required', 'string'],
            'reaction_id' => $ruleReaction,
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        return $this->handleReactionId($data);
    }

    protected function handleReactionId(array $data): array
    {
        $reactionId = Arr::get($data, 'reaction_id');
        if ($reactionId == null) {
            /** @var Reaction $reaction */
            $reaction = Reaction::query()
                ->where('is_active', '=', 1)
                ->orderBy('ordering')
                ->orderBy('id')
                ->firstOrFail();

            Arr::set($data, 'reaction_id', $reaction->entityId());
            return $data;
        }

        /**@deprecated v1.9 remove "toCompatibleData" */
        Arr::set($data, 'reaction_id', MobileAppAdapter::toCompatibleData($reactionId, 'v1.8'));

        return $data;
    }
}
