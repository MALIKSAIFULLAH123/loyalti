<?php

namespace MetaFox\App\Http\Resources\v1\AppStoreProduct\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Storage\Repositories\AssetRepositoryInterface;

/**
 * Class StoreAppDetail.
 * @property array<string, mixed> $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class StoreAppDetail extends JsonResource
{
    private array $casts = [
        'rated'           => 'int',
        'total_reviews'   => 'int',
        'total_rated'     => 'int',
        'total_installed' => 'int',
    ];

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $data = array_merge($this->getNullObject(), $this->resource);

        foreach ($data as $key => $value) {
            $type = Arr::get($this->casts, $key);

            $data[$key] = match ($type) {
                'int', 'integer' => (int) $value,
                'bool', 'boolean' => (bool) $value,
                'string' => (string) $value,
                default  => $value,
            };
        }

        return $data;
    }

    protected function getNullObject(): array
    {
        return [
            'module_name'            => 'app',
            'resource_name'          => 'app_store_product',
            'purchase_url'           => null,
            'version'                => 'N/A',
            'is_installed'           => false,
            'label_install'          => __p('app::phrase.install'),
            'bundle_status'          => 'unknown',
            'current_version'        => 'N/A',
            'identity'               => 'test',
            'name'                   => __p('app::phrase.app_not_found_label'),
            'description'            => __p('app::phrase.app_not_found_desc'),
            'type'                   => 'app',
            'text_detail'            => '',
            'text_changelog'         => '',
            'text_installation'      => '',
            'price'                  => '0',
            'pricing_type'           => 'perpetual',
            'pricing_type_label'     => __p('app::phrase.price_type_perpetual'),
            'renewal_fee'            => 0,
            'discount'               => '',
            'is_featured'            => false,
            'rated'                  => 0,
            'total_reviews'          => 0,
            'total_rated'            => 0,
            'total_installed'        => 0,
            'latest_version'         => 'N/A',
            'version_detail'         => [],
            'updated_at'             => '',
            'created_at'             => '',
            'compatible'             => 'N/A',
            'mobile_support'         => false,
            'mobile_compatible'      => '',
            'demo_url'               => '',
            'term_url'               => '',
            'author'                 => [],
            'categories'             => [],
            'url'                    => '',
            'image'                  => [],
            'icon'                   => [],
            'images'                 => [],
            'user'                   => [],
            'can_install'            => false,
            'can_purchase'           => false,
            'expired_at'             => '',
            'can_upgrade'            => false,
            'has_processing_payment' => false,
            'current_version'        => '',
        ];
    }
}
