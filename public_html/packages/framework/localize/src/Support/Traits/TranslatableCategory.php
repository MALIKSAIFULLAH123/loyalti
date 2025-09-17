<?php

namespace MetaFox\Localize\Support\Traits;

/**
 * @property array<string, string>|array<string> $translatableAttributes
 * @property string|null                         $name
 * @property string|null                         $name_var
 * @property string|null                         $label
 */
trait TranslatableCategory
{
    use HasTranslatableAttributes;

    protected $translatableAttributes = ['name'];

    public function getNameAttribute($value): string
    {
        $name = is_string($value) ? __p($value) : $value;

        return  $value === $name ? __translation_wrapper(__translation_prefix($name, $name)) : $name;
    }

    public function getNameVarAttribute(): ?string
    {
        return $this->attributes['name'] ?? null;
    }

    public function getLabelAttribute($value): ?string
    {
        return $value ? __p($value) : $this->name;
    }
}
