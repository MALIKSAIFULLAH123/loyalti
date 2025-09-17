<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

/**
 * Class FormValidationSchema.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class FormValidationSchema extends JsonResource
{
    /**
     * @var array<string,mixed>
     */
    protected array $schema = [
        'type'       => 'object',
        'properties' => [],
    ];

    /**
     * @param string $name
     */
    private function fixNestedName(string $name): void
    {
        if (!strpos($name, '.')) {
            return;
        }

        /** @var string $parent */
        $parent = preg_replace("#\.([^\.])+$#", '', $name);
        $key = str_replace('.', '.properties.', $parent);

        if (!Arr::get($this->schema['properties'], $key)) {
            Arr::set($this->schema['properties'], $key, ['type' => 'object']);
        }
    }

    /**
     * @param string                             $name
     * @param ?string                            $label
     * @param array<string, array<string,mixed>> $validation
     */
    public function add(string $name, ?string $label, array $validation): void
    {
        if (!isset($validation['type'])) {
            $validation['type'] = 'string';
        }

        if (!isset($validation['label']) && $label) {
            $validation['label'] = $label;
        }

        $this->fixNestedName($name);

        $key = str_replace('.', '.properties.', $name);
        Arr::set($this->schema['properties'], $key, $validation);
    }

    /**
     * @param Request $request
     *
     * @return array<string,mixed>
     * @SuppressWarnings (PHPMD.UnusedFormalParameter)
     */
    public function toArray($request)
    {
        return $this->schema;
    }
}
