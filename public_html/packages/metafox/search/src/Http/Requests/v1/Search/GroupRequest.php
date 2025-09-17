<?php

namespace MetaFox\Search\Http\Requests\v1\Search;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Search\Repositories\SearchRepositoryInterface;
use MetaFox\Search\Support\Support;

class GroupRequest extends FormRequest
{
    public function rules(): array
    {
        return $this->getStandardRules();
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        $data = $this->handleExtra($data);

        $data = $this->handleHashtag($data);

        return $data;
    }

    protected function handleHashtag(array $data): array
    {
        $isHashtag = false;

        Arr::set($data, 'view_context', Support::VIEW_SEARCH);

        if (Arr::has($data, 'is_hashtag')) {
            $isHashtag = Arr::get($data, 'is_hashtag', false);

            unset($data['is_hashtag']);
        }

        if (!$isHashtag) {
            return $data;
        }

        if (Arr::has($data, 'hashtag')) {
            Arr::set($data, 'q', Arr::get($data, 'hashtag'));
            unset($data['hashtag']);
        }

        $q = Arr::get($data, 'q');

        if (MetaFoxConstant::EMPTY_STRING != $q) {
            $q = ltrim($q, '#');
        }

        return array_merge($data, [
            'q'            => $q,
            'view_context' => Support::VIEW_HASHTAG,
        ]);
    }

    public function messages(): array
    {
        $min = Settings::get('core.general.min_character_to_search');

        return [
            'q.required' => __p('search::phrase.please_input_your_keywords_to_search'),
            'q.string'   => __p('search::phrase.please_input_your_keywords_to_search'),
            'q.min'      => __p('search::phrase.the_keyword_must_contain_at_least_number_characters', [
                'number' => $min,
            ]),
        ];
    }

    protected function getWhenOptions(): array
    {
        $options = resolve(SearchRepositoryInterface::class)->getWhenOptions();

        return array_column($options, 'value');
    }

    protected function getStandardRules(): array
    {
        $min = Settings::get('core.general.min_character_to_search');

        return [
            'q'                           => ['required', 'string', 'min:' . $min],
            'owner_id'                    => ['sometimes', 'numeric', 'exists:user_entities,id'],
            'from'                        => ['sometimes', 'string'],
            'hashtag'                     => ['sometimes', 'string'],
            'related_comment_friend_only' => ['sometimes', new AllowInRule([0, 1])],
            'is_hashtag'                  => ['sometimes', new AllowInRule([0, 1])],
            'when'                        => ['sometimes', new AllowInRule($this->getWhenOptions())],
        ];
    }

    protected function handleExtra(array $data): array
    {
        Arr::set($data, 'is_hashtag', (bool) Arr::get($data, 'is_hashtag', false));

        Arr::set($data, 'related_comment_friend_only', (bool) Arr::get($data, 'related_comment_friend_only', false));

        if (!Arr::has($data, 'q')) {
            Arr::set($data, 'q', MetaFoxConstant::EMPTY_STRING);
        }

        if (!Arr::has($data, 'from')) {
            Arr::set($data, 'from', Browse::VIEW_ALL);
        }

        if (!Arr::has($data, 'owner_id')) {
            Arr::set($data, 'owner_id', 0);
        }

        if (!Arr::has($data, 'when')) {
            Arr::set($data, 'when', Browse::VIEW_ALL);
        }

        return $data;
    }
}
