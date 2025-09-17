<?php

namespace MetaFox\Mobile\Http\Requests\v1\AdMobConfig\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Mobile\Models\AdMobConfig;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Mobile\Http\Controllers\Api\v1\AdMobConfigAdminController::store
 * stub: /packages/requests/api_action_request.stub
 */

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
        $allowTypes          = array_keys(AdMobConfig::AD_MOB_TYPE);
        $allowLocations      = array_keys(AdMobConfig::AD_MOB_LOCATIONS);
        $allowFrequencyTypes = array_keys(AdMobConfig::AD_MOB_FREQUENCY);
        $allowFrequency      = array_keys(AdMobConfig::AD_MOB_TIME_FREQUENCY);

        return [
            'name'                    => ['required', 'string'],
            'type'                    => ['required', 'string', new AllowInRule($allowTypes)],
            'location'                => ['required_if:type,banner', 'string', new AllowInRule($allowLocations)],
            'frequency_capping'       => ['required', 'string', new AllowInRule($allowFrequencyTypes)],
            'time_capping_impression' => ['sometimes', 'numeric', 'exclude_unless:frequency_capping,times'],
            'time_capping_frequency'  => ['sometimes', 'string', 'exclude_unless:frequency_capping,times', new AllowInRule($allowFrequency)],
            'view_capping'            => ['sometimes', 'numeric', 'exclude_unless:frequency_capping,views'],
            'pages'                   => ['required', 'array', 'min:1'],
            'pages.*'                 => ['required', 'integer', 'exists:ad_mob_pages,id'],
            'roles'                   => ['sometimes', 'array'],
            'is_active'               => ['sometimes', 'numeric', new AllowInRule([0, 1])],
            'is_sticky'               => ['sometimes', 'numeric', new AllowInRule([0, 1])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = Arr::add($data, 'is_active', 1);

        $data = $this->transformRolesData($data);
        $data = $this->transformLocationData($data);

        $type = Arr::get($data, 'type');

        return array_merge($data, $this->getFilters($type));
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function transformRolesData(array $data): array
    {
        if (!Arr::has($data, 'roles')) {
            $allRoles = resolve(RoleRepositoryInterface::class)->getUsableRoles();
            $data     = Arr::set($data, 'roles', $allRoles->pluck('id')->toArray());
        }

        return $data;
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     * @TODO: Implement multiple ad on 1 page??!
     */
    protected function transformLocationData(array $data): array
    {
        $priority = 1; //@TODO: There will be more if supported multiple ad on 1 page

        $location = Arr::get($data, 'location', AdMobConfig::AD_MOB_LOCATION_TOP);

        $data['location_priority'] = [
            'location' => $location,
            'priority' => $priority,
        ];

        return Arr::except($data, ['location', 'priority']);
    }

    protected function getFilters(string $type = AdMobConfig::AD_MOB_TYPE_BANNER): array
    {
        return match ($type) {
            AdMobConfig::AD_MOB_TYPE_BANNER => [],
            default                         => [
                'frequency_capping'       => null,
                'time_capping_impression' => null,
                'time_capping_frequency'  => null,
                'view_capping'            => null,
            ],
        };
    }
}
