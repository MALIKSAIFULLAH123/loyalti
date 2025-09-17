<?php

namespace MetaFox\Core\Http\Resources\v1\Link;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use MetaFox\Core\Models\Link;
use MetaFox\Form\AbstractForm;
use MetaFox\Platform\Rules\AllowInRule;

class CreateFeedForm extends AbstractForm
{
    /**
     * @param  Request                                    $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validated(Request $request): array
    {
        $data = $request->all();

        $rules = $this->getValidationRules();

        $validator = Validator::make($data, $rules, [
            'user_status.required_if' => __p('activity::validation.add_some_text_to_share'),
        ]);

        $data = array_merge($validator->validate(), [
            'user_status' => Arr::get($data, 'user_status', ''),
        ]);

        return $this->transformData($data);
    }

    /**
     * @return array
     */
    protected function getValidationRules(): array
    {
        return [
            'link_image'        => ['required_if:post_type,' . Link::FEED_POST_TYPE . '|max:10240|mimes:png,jpg'],
            'link_url'          => ['sometimes'],
            'link_description'  => ['sometimes'],
            'link_title'        => ['sometimes'],
            'link_embed_code'   => ['sometimes'],
            'is_preview_hidden' => ['sometimes', new AllowInRule([0, 1])],
            'user_status'       => ['required_if:is_preview_hidden,0'],
        ];
    }

    protected function transformData(array $data): array
    {
        if (!Arr::has($data, 'content')) {
            Arr::set($data, 'content', Arr::get($data, 'user_status', ''));
        }

        Arr::set($data, 'is_preview_hidden', (bool) Arr::get($data, 'is_preview_hidden', false));

        return $data;
    }
}
