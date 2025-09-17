<?php

namespace MetaFox\Photo\Http\Requests\v1\Photo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Repositories\CategoryRepositoryInterface;
use MetaFox\Photo\Support\Facades\Photo as PhotoFacade;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\CategoryRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PrivacyRule;
use MetaFox\Platform\Traits\Http\Request\PrivacyRequestTrait;

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    use PrivacyRequestTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'title'            => ['sometimes', 'string'],
            'categories'       => ['sometimes', 'array'],
            'categories.*'     => ['numeric', new CategoryRule(resolve(CategoryRepositoryInterface::class), $this->route('photo'))],
            'album'            => [
                'sometimes', 'nullable', 'numeric', new ExistIfGreaterThanZero('exists:photo_albums,id'),
            ],
            'tags'             => ['sometimes', 'array'],
            'tags.*'           => ['string'],
            'text'             => ['nullable', 'string'],
            'privacy'          => ['sometimes', new PrivacyRule([
                'validate_privacy_list' => false,
            ])],
            'location'         => [
                'sometimes', 'array',
            ],
            //todo location rule should be re-use able.
            'location.address' => 'string',
            'location.lat'     => 'numeric',
            'location.lng'     => 'numeric',
        ];

        return $this->handleMatureContent($rules);
    }

    private function handleMatureContent(array $rules): array
    {
        $context = user();

        if (!$context->hasPermissionTo('photo.add_mature_image')) {
            return $rules;
        }

        $photo = Photo::findOrFail($this->route('photo'));

        if ($photo->is_profile_photo) {
            return $rules;
        }

        if ($photo->is_cover_photo) {
            return $rules;
        }

        $rules['mature'] = ['sometimes', 'nullable', 'numeric', new AllowInRule(PhotoFacade::getAllowMatureContent())];

        return $rules;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        $data = $this->handlePrivacy($data);

        if (!empty($data['location'])) {
            $data['location_name']      = $data['location']['address'];
            $data['location_latitude']  = $data['location']['lat'];
            $data['location_longitude'] = $data['location']['lng'];
            $data['location_address'] = $data['location']['full_address'];
            unset($data['location']);
        }

        if (isset($data['album'])) {
            $data['album_id'] = $data['album'];
        }

        if (Arr::has($data, 'text')) {
            $text = Arr::get($data, 'text');

            if (null === $text) {
                Arr::set($data, 'text', MetaFoxConstant::EMPTY_STRING);
            }
        }

        return $data;
    }

    /**
     * @return array<mixed>
     */
    public function messages(): array
    {
        return [
            'title.string' => __p('core::validation.name.required'),
        ];
    }
}
