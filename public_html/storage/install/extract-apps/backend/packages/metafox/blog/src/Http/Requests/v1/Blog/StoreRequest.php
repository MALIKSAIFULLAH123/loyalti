<?php

namespace MetaFox\Blog\Http\Requests\v1\Blog;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Blog\Repositories\CategoryRepositoryInterface;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\CategoryRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PrivacyRule;
use MetaFox\Platform\Rules\ResourceNameRule;
use MetaFox\Platform\Rules\ResourceTextRule;
use MetaFox\Platform\Traits\Http\Request\AttachmentRequestTrait;
use MetaFox\Platform\Traits\Http\Request\PrivacyRequestTrait;
use MetaFox\Platform\Traits\Http\Request\TagRequestTrait;

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    use PrivacyRequestTrait;
    use AttachmentRequestTrait;
    use TagRequestTrait;

    public const ACTION_CAPTCHA_NAME = 'create-blog';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'title'          => ['required', 'string', new ResourceNameRule('blog')],
            'categories'     => ['sometimes', 'array'],
            'categories.*'   => ['numeric', new CategoryRule(resolve(CategoryRepositoryInterface::class))],
            'owner_id'       => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
            'file'           => ['sometimes', 'array'],
            'file.temp_file' => ['required_with:file', 'numeric', new ExistIfGreaterThanZero('exists:storage_files,id')],
            'text'           => ['required', 'string', new ResourceTextRule(true)],
            'draft'          => ['sometimes', 'numeric', new AllowInRule([0, 1])],
            'tags'           => ['sometimes', 'array'],
            'tags.*'         => ['string'],
            'privacy'        => ['required', new PrivacyRule()],
            'captcha'        => Captcha::ruleOf('blog.create_blog'),
        ];

        $rules            = $this->applyAttachmentRules($rules);
        $rules            = $this->applyTagRules($rules);
        $rules['captcha'] = Captcha::ruleOf('blog.create_blog');

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = $this->handlePrivacy($data);

        $data['is_draft'] = 0;
        if (isset($data['draft'])) {
            $data['is_draft'] = $data['draft'];
        }

        $data['temp_file'] = 0;
        if (isset($data['file']['temp_file'])) {
            $data['temp_file'] = $data['file']['temp_file'];
        }

        if (empty($data['owner_id'])) {
            $data['owner_id'] = 0;
        }

        if (array_key_exists('tags', $data)) {
            $data['tags'] = parse_input()->extractResourceTopic($data['tags']);
        }

        return $data;
    }
}
