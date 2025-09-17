<?php

namespace MetaFox\Activity\Http\Resources\v1\Post;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use MetaFox\Activity\Models\Post;
use MetaFox\Form\AbstractForm;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Eloquent\Model\HasFilterTagUserTrait;

class CreateFeedForm extends AbstractForm
{
    use HasFilterTagUserTrait;

    /**
     * @var bool
     */
    protected $isEdit;

    /**
     * @var bool
     */
    protected bool $isEditSchedule;

    /**
     * @param      $resource
     * @param bool $isEdit
     */
    public function __construct($resource = null, bool $isEdit = false, bool $isEditSchedule = false)
    {
        parent::__construct($resource);

        $this->isEdit           = $isEdit;
        $this->isEditSchedule   = $isEditSchedule;
    }

    /**
     * @param  Request                                    $request
     * @return array|null
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validated(Request $request): array
    {
        $data = $request->all();

        $rules = $this->getValidationRules($data);

        $transformedData = $data;

        if (count($rules)) {
            $messages = $this->getValidationMessages();

            $validation = Validator::make($data, $rules, $messages);

            $transformedData = $validation->validate();
        }

        return $this->transformData($transformedData);
    }

    /**
     * @param  array $data
     * @return array
     */
    protected function getValidationRules(array $data): array
    {
        $location = Arr::get($data, 'location');

        $tagFriends = Arr::get($data, 'tagged_friends', []);

        if (null !== $location || !empty($tagFriends)) {
            return [];
        }

        return [
            'user_status' => ['required_if:post_type,' . Post::FEED_POST_TYPE],
        ];
    }

    /**
     * @return array
     */
    protected function getValidationMessages(): array
    {
        return [
            'user_status.required_if' => __p('activity::validation.add_some_text_to_share'),
        ];
    }

    protected function transformData(array $data): array
    {
        $status = Arr::get($data, 'user_status');

        if (null === $status) {
            $status = MetaFoxConstant::EMPTY_STRING;
        }

        if (!Arr::has($data, 'content')) {
            Arr::set($data, 'content', trim($status));
        }

        if (is_numeric($showMap = Arr::get($data, 'location.show_map'))) {
            Arr::set($data, 'close_map_on_feed', (int) $showMap === 0);
        }

        return $data;
    }

    /**
     * Using to validate only.
     * @param  array                                      $data
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate(array $data): void
    {
        $rules = $this->getValidationRules($data);

        if (!count($rules)) {
            return;
        }

        $messages = $this->getValidationMessages();

        $validation = Validator::make($data, $rules, $messages);

        $validation->validate();
    }
}
