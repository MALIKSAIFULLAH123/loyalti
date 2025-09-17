<?php

namespace MetaFox\Activity\Http\Resources\v1\Share;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class FeedShareForm extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function toArray($request): array
    {
        $postType = $request->get('post_type');

        $fields = [
            'item_type'    => [
                'name'      => 'item_type',
                'component' => 'Hidden',
                'value'     => $request->get('item_type'),
            ],
            'item_id'      => [
                'name'      => 'item_id',
                'component' => 'Hidden',
                'value'     => $request->get('item_id'),
            ],
            'post_type'    => [
                'name'      => 'post_type',
                'component' => 'Hidden',
                'value'     => $postType,
            ],
            'post_content' => [
                'name'          => 'post_content',
                'component'     => 'TextArea',
                'value'         => '',
                'returnKeyType' => 'default',
                'label'         => 'Message',
                'placeholder'   => 'Write a message...',
                'autoFocus'     => true,
            ],
        ];

        $extraFields = app('events')->dispatch('activity.share.form', [$postType], true);

        if (is_array($extraFields) && count($extraFields)) {
            $fields = array_merge($fields, $extraFields);
        }

        $fields['submit'] = [
            'name'      => 'submit',
            'component' => 'Submit',
            'label'     => 'share',
        ];

        return [
            'title'       => 'Share',
            'description' => '',
            'action'      => 'mobile/feed/share',
            'method'      => 'post',
            'fields'      => $fields,
        ];
    }

    /**
     * @param  Request $request
     * @return array
     */
    public function validated(Request $request): array
    {
        $data = $request->all();

        return $this->transformData($data);
    }

    /**
     * @param  array $data
     * @return array
     */
    protected function transformData(array $data): array
    {
        if (!Arr::has($data, 'content')) {
            Arr::set($data, 'content', Arr::get($data, 'user_status', ''));
        }

        return $data;
    }
}
